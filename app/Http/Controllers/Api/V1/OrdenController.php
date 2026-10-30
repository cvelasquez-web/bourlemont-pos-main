<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EstadoOrden;
use App\Enums\MetodoPago;
use App\Http\Controllers\Controller;
use App\Models\DetalleOrden;
use App\Models\Orden;
use App\Models\Producto;
use App\Models\TransaccionPago;
use App\Services\EscandalloService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrdenController extends Controller
{
    protected $escandalloService;

    public function __construct(EscandalloService $escandalloService)
    {
        $this->escandalloService = $escandalloService;
    }

    public function crear(Request $request)
    {
        $datos = $request->validate([
            'numero_mesa' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.id_producto' => 'required|integer|exists:productos,id_producto',
            'items.*.cantidad' => 'required|integer|min:1|max:999',
        ]);

        try {
            $orden = DB::transaction(function () use ($datos, $request) {
                $orden = Orden::create([
                    'numero_mesa' => $datos['numero_mesa'],
                    'id_usuario_creacion' => $request->user()->id_usuario,
                    'estado' => EstadoOrden::ABIERTA,
                    'total' => 0,
                    'fecha_creacion' => Carbon::now('America/Guatemala'),
                ]);

                $productos = Producto::whereIn('id_producto', collect($datos['items'])->pluck('id_producto'))
                    ->get()
                    ->keyBy('id_producto');

                $total = 0;
                foreach ($datos['items'] as $it) {
                    $prod = $productos[$it['id_producto']];
                    $subtotal = round((float) $prod->precio_venta * $it['cantidad'], 2);
                    $total += $subtotal;

                    DetalleOrden::create([
                        'id_orden' => $orden->id_orden,
                        'id_producto' => $prod->id_producto,
                        'cantidad' => $it['cantidad'],
                        'precio_unitario' => $prod->precio_venta,
                        'subtotal' => $subtotal,
                    ]);
                }

                $orden->total = round($total, 2);
                $orden->save();

                return $orden;
            });

            return response()->json($orden->load('items.producto'), 201);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al crear la orden.');
        }
    }

    public function cobrar(Request $request, $id_orden)
    {
        $datos = $request->validate([
            'metodo_pago' => ['required', Rule::enum(MetodoPago::class)],
            'monto_pagado' => 'required|numeric|min:0',
            'propina' => 'nullable|numeric|min:0',
        ]);

        try {
            [$orden, $pago, $alertas] = DB::transaction(function () use ($datos, $request, $id_orden) {
                $orden = Orden::where('id_orden', $id_orden)->lockForUpdate()->first();

                if (!$orden) {
                    throw new \DomainException('Orden no encontrada.', 404);
                }
                if ($orden->estado !== EstadoOrden::ABIERTA) {
                    throw new \DomainException('La orden ya se encuentra pagada o anulada.', 409);
                }
                if (round((float) $datos['monto_pagado'], 2) < round((float) $orden->total, 2)) {
                    throw new \DomainException('El monto pagado es menor al total de la orden.', 422);
                }

                // 1. Descontar inventario
                $alertas = $this->escandalloService->deducirInventario($orden);

                // 2. Registrar pago
                $pago = TransaccionPago::create([
                    'id_orden' => $orden->id_orden,
                    'id_usuario_cobro' => $request->user()->id_usuario,
                    'metodo_pago' => $datos['metodo_pago'],
                    'monto_pagado' => $datos['monto_pagado'],
                    'propina' => $datos['propina'] ?? 0,
                    'fecha_transaccion' => Carbon::now('America/Guatemala'),
                ]);

                // 3. Cerrar orden
                $orden->estado = EstadoOrden::PAGADA;
                $orden->fecha_cierre = Carbon::now('America/Guatemala');
                $orden->save();

                return [$orden, $pago, $alertas];
            });
        } catch (\DomainException $e) {
            return response()->json(['detail' => $e->getMessage()], $e->getCode() ?: 422);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Fallo crítico en el cobro.');
        }

        // El ticket va fuera de la transacción: si falla, el cobro ya es válido.
        try {
            $orden->load('items.producto');
            $ticketTexto = $this->generarTicketTermico($orden, $pago);
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            $ticketTexto = null;
        }

        return response()->json([
            'success' => true,
            'mensaje' => 'Orden cobrada exitosamente y stock actualizado.',
            'orden' => $orden,
            'alertas_inventario' => $alertas,
            'ticket_termico' => $ticketTexto,
        ]);
    }

    private function errorInterno(\Throwable $e, string $mensaje)
    {
        Log::error($e->getMessage(), ['exception' => $e]);
        return response()->json(['detail' => $mensaje], 500);
    }

    /**
     * Genera el texto formateado para la impresora térmica de 80mm (42 columnas)
     */
    private function generarTicketTermico($orden, $pago)
    {
        $ancho = 42;
        $linea = str_repeat("=", $ancho);
        $lineaDelgada = str_repeat("-", $ancho);
        $fecha = Carbon::now('America/Guatemala')->format('d/m/Y H:i:s');

        $usuario = \App\Models\Usuario::find($orden->id_usuario_creacion);
        $bartender = $usuario ? $usuario->nombre : 'BARTENDER';

        $subtotalSinIva = (float) $orden->total / 1.12;
        $iva = (float) $orden->total - $subtotalSinIva;

        $ticket = [];
        $ticket[] = $linea;
        $ticket[] = str_pad("BOURLEMONT CHAMPAGNE BAR", $ancho, " ", STR_PAD_BOTH);
        $ticket[] = str_pad("Exclusividad & Alta Cocteleria", $ancho, " ", STR_PAD_BOTH);
        $ticket[] = str_pad("Ciudad Vieja, Sacatepequez", $ancho, " ", STR_PAD_BOTH);
        $ticket[] = str_pad("NIT: 98765432-1", $ancho, " ", STR_PAD_BOTH);
        $ticket[] = $linea;
        $ticket[] = "ORDEN #" . str_pad($orden->id_orden, 5, "0", STR_PAD_LEFT) . "   |   " . $orden->numero_mesa;
        $ticket[] = "Fecha: " . $fecha;
        $ticket[] = "Atendio: " . $bartender;
        $ticket[] = "Cliente: CONSUMIDOR FINAL";
        $ticket[] = "NIT: CF";
        $ticket[] = $lineaDelgada;
        $ticket[] = "CANT  DESCRIPCION             TOTAL (Q)";
        $ticket[] = $lineaDelgada;

        foreach ($orden->items as $item) {
            $prodName = $item->producto ? $item->producto->nombre : "Prod " . $item->id_producto;

            if (strlen($prodName) > 23) {
                $prodName = substr($prodName, 0, 20) . "...";
            }

            $cantStr = sprintf("%2dx", $item->cantidad);
            $precioStr = number_format($item->subtotal, 2, '.', '');
            $espacio = max($ancho - strlen($cantStr) - strlen($precioStr) - 2, 1);

            $ticket[] = $cantStr . " " . str_pad($prodName, $espacio, " ") . " Q" . str_pad($precioStr, 7, " ", STR_PAD_LEFT);
        }

        $ticket[] = $lineaDelgada;
        $ticket[] = "SUBTOTAL (Sin IVA):       Q " . str_pad(number_format($subtotalSinIva, 2, '.', ''), 9, " ", STR_PAD_LEFT);
        $ticket[] = "IVA (12%):                Q " . str_pad(number_format($iva, 2, '.', ''), 9, " ", STR_PAD_LEFT);
        $ticket[] = "TOTAL CUENTA:             Q " . str_pad(number_format($orden->total, 2, '.', ''), 9, " ", STR_PAD_LEFT);

        if ($pago->propina > 0) {
            $ticket[] = "PROPINA VOLUNTARIA:       Q " . str_pad(number_format($pago->propina, 2, '.', ''), 9, " ", STR_PAD_LEFT);
            $totalConPropina = (float) $orden->total + (float) $pago->propina;
            $ticket[] = "TOTAL CON PROPINA:        Q " . str_pad(number_format($totalConPropina, 2, '.', ''), 9, " ", STR_PAD_LEFT);
        }

        $ticket[] = $lineaDelgada;
        $ticket[] = "METODO DE PAGO: " . $pago->metodo_pago->value;
        $ticket[] = "PAGADO: Q " . number_format($pago->monto_pagado, 2, '.', '');

        $ticket[] = $linea;
        $ticket[] = str_pad("DOCUMENTO TRIBUTARIO FEL", $ancho, " ", STR_PAD_BOTH);
        $ticket[] = str_pad("Autorizacion SAT: (Pendiente migrar FEL)", $ancho, " ", STR_PAD_BOTH);
        $ticket[] = $linea;
        $ticket[] = str_pad("Gracias por su distinguida visita!", $ancho, " ", STR_PAD_BOTH);
        $ticket[] = str_pad("Bourlemont Champagne Bar", $ancho, " ", STR_PAD_BOTH);
        $ticket[] = $linea;

        return implode("\n", $ticket);
    }
}