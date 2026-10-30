<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\EstadoOrden;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOrdenRequest;
use App\Http\Requests\Api\V1\UpdateOrdenRequest;
use App\Http\Requests\Api\V1\CobrarOrdenRequest;
use App\Models\DetalleOrden;
use App\Models\Orden;
use App\Models\Producto;
use App\Models\TransaccionPago;
use App\Services\EscandalloService;
use App\Services\TicketService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrdenController extends Controller
{
    protected $escandalloService;
    protected $ticketService;

    public function __construct(EscandalloService $escandalloService, TicketService $ticketService)
    {
        $this->escandalloService = $escandalloService;
        $this->ticketService = $ticketService;
    }

    /**
     * Lista todas las órdenes (filtrable por estado).
     */
    public function index(Request $request)
    {
        try {
            $query = Orden::with(['items.producto', 'usuario_creacion'])->orderBy('id_orden', 'desc');
            
            if ($request->has('estado') && in_array($request->query('estado'), ['ABIERTA', 'PAGADA', 'ANULADA'])) {
                $query->where('estado', $request->query('estado'));
            }

            return response()->json($query->get());
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al listar órdenes.');
        }
    }

    /**
     * Muestra una orden en específico.
     */
    public function show($id)
    {
        try {
            $orden = Orden::with(['items.producto', 'usuario_creacion'])->find($id);
            if (!$orden) {
                return response()->json(['detail' => 'Orden no encontrada.'], 404);
            }
            return response()->json($orden);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al obtener la orden.');
        }
    }

    /**
     * Crea una nueva orden.
     */
    public function crear(StoreOrdenRequest $request)
    {
        $datos = $request->validated();

        try {
            $orden = DB::transaction(function () use ($datos, $request) {
                $orden = Orden::create([
                    'numero_mesa' => $datos['numero_mesa'],
                    'id_usuario_creacion' => $request->user()->id_usuario,
                    'estado' => EstadoOrden::ABIERTA,
                    'total' => 0,
                    'fecha_creacion' => Carbon::now('America/Guatemala'),
                ]);

                $this->procesarItems($orden, $datos['items']);
                return $orden;
            });

            return response()->json($orden->load('items.producto'), 201);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al crear la orden.');
        }
    }

    /**
     * Actualiza una orden existente (solo si está ABIERTA).
     */
    public function update(UpdateOrdenRequest $request, $id)
    {
        $datos = $request->validated();

        try {
            $orden = DB::transaction(function () use ($datos, $id) {
                $orden = Orden::where('id_orden', $id)->lockForUpdate()->first();

                if (!$orden) {
                    throw new \DomainException('Orden no encontrada.', 404);
                }
                if ($orden->estado !== EstadoOrden::ABIERTA) {
                    throw new \DomainException('Solo se pueden modificar órdenes abiertas.', 409);
                }

                // Actualizamos la mesa
                $orden->numero_mesa = $datos['numero_mesa'];
                
                // Borramos los items viejos y los volvemos a crear
                DetalleOrden::where('id_orden', $orden->id_orden)->delete();
                $this->procesarItems($orden, $datos['items']);
                
                return $orden;
            });

            return response()->json($orden->load('items.producto'));
        } catch (\DomainException $e) {
            return response()->json(['detail' => $e->getMessage()], $e->getCode() ?: 400);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al actualizar la orden.');
        }
    }

    /**
     * Anula una orden sin cobrarla.
     */
    public function anular($id)
    {
        try {
            $orden = Orden::where('id_orden', $id)->lockForUpdate()->first();

            if (!$orden) {
                return response()->json(['detail' => 'Orden no encontrada.'], 404);
            }
            if ($orden->estado !== EstadoOrden::ABIERTA) {
                return response()->json(['detail' => 'Solo se pueden anular órdenes abiertas.'], 409);
            }

            $orden->estado = EstadoOrden::ANULADA;
            $orden->save();

            return response()->json(['mensaje' => 'Orden anulada exitosamente.']);
        } catch (\Throwable $e) {
            return $this->errorInterno($e, 'Error al anular la orden.');
        }
    }

    /**
     * Cobra una orden y genera el ticket.
     */
    public function cobrar(CobrarOrdenRequest $request, $id_orden)
    {
        $datos = $request->validated();

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

                $alertas = $this->escandalloService->deducirInventario($orden);

                $pago = TransaccionPago::create([
                    'id_orden' => $orden->id_orden,
                    'id_usuario_cobro' => $request->user()->id_usuario,
                    'metodo_pago' => $datos['metodo_pago'],
                    'monto_pagado' => $datos['monto_pagado'],
                    'propina' => $datos['propina'] ?? 0,
                    'fecha_transaccion' => Carbon::now('America/Guatemala'),
                ]);

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

        try {
            $orden->load('items.producto');
            $ticketTexto = $this->ticketService->generarTicketTermico($orden, $pago);
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

    /**
     * Método privado para procesar los items y calcular el total.
     */
    private function procesarItems(Orden $orden, array $items)
    {
        $productos = Producto::whereIn('id_producto', collect($items)->pluck('id_producto'))
            ->get()
            ->keyBy('id_producto');

        $total = 0;
        foreach ($items as $it) {
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
    }

    private function errorInterno(\Throwable $e, string $mensaje)
    {
        Log::error($e->getMessage(), ['exception' => $e]);
        return response()->json(['detail' => $mensaje], 500);
    }
}