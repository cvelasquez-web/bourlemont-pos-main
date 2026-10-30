<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\MetodoPago;
use App\Http\Controllers\Controller;
use App\Models\BitacoraAuditoria;
use App\Models\TransaccionPago;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuditoriaController extends Controller
{
    /**
     * Lista los pagos para la tabla de auditoría (por defecto los últimos 500).
     */
    public function listarPagos(Request $request)
    {
        try {
            $limite = min(max((int) $request->query('limite', 500), 1), 2000);

            $pagos = TransaccionPago::with(['orden', 'usuarioCobro'])
                ->orderBy('id_pago', 'desc')
                ->limit($limite)
                ->get();

            $data = $pagos->map(function ($p) {
                return [
                    'id_pago' => $p->id_pago,
                    'id_orden' => $p->id_orden,
                    'numero_mesa' => $p->orden ? $p->orden->numero_mesa : 'Mesa Desconocida',
                    'nombre_bartender' => $p->usuarioCobro ? $p->usuarioCobro->nombre : 'Desconocido',
                    'fecha_transaccion' => $p->fecha_transaccion ? $p->fecha_transaccion->format('Y-m-d H:i:s') : 'N/A',
                    'metodo_pago' => $p->metodo_pago ? $p->metodo_pago->value : 'DESCONOCIDO',
                    'monto_pagado' => (float) $p->monto_pagado,
                    'propina' => (float) $p->propina,
                ];
            });

            return response()->json($data);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    /**
     * Lista la bitácora de acciones (últimos 100 registros).
     */
    public function listarLogs()
    {
        try {
            $logs = BitacoraAuditoria::with('usuario')
                ->orderBy('id_log', 'desc')
                ->limit(100)
                ->get();

            $data = $logs->map(function ($l) {
                return [
                    'id_log' => $l->id_log,
                    'accion' => $l->accion,
                    'descripcion' => $l->descripcion,
                    'fecha' => $l->fecha ? $l->fecha->format('Y-m-d H:i:s') : 'N/A',
                    'nombre_usuario' => $l->usuario ? $l->usuario->nombre : 'Sistema',
                ];
            });

            return response()->json($data);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    /**
     * Cambia el método de pago de una transacción y lo registra en la bitácora.
     */
    public function cambiarMetodoPago(Request $request)
    {
        $datos = $request->validate([
            'id_pago' => 'required|integer',
            'nuevo_metodo_pago' => ['required', Rule::enum(MetodoPago::class)],
            'motivo' => 'required|string|min:5|max:500',
        ]);

        try {
            $resultado = DB::transaction(function () use ($datos, $request) {
                $pago = TransaccionPago::lockForUpdate()->find($datos['id_pago']);
                if (!$pago) {
                    return 'no_encontrado';
                }

                $nuevo = MetodoPago::from($datos['nuevo_metodo_pago']);
                $anterior = $pago->metodo_pago;

                if ($anterior === $nuevo) {
                    return 'sin_cambio';
                }

                $pago->metodo_pago = $nuevo;
                $pago->save();

                BitacoraAuditoria::create([
                    'id_usuario' => $request->user()->id_usuario,
                    'accion' => 'CAMBIO_METODO_PAGO',
                    'descripcion' => "Pago #{$pago->id_pago} (Orden #{$pago->id_orden}) modificado de "
                        . ($anterior ? $anterior->value : 'DESCONOCIDO')
                        . " a {$nuevo->value}. Motivo: {$datos['motivo']}",
                    'fecha' => Carbon::now('America/Guatemala'),
                ]);

                return 'ok';
            });

            if ($resultado === 'no_encontrado') {
                return response()->json(['message' => 'Pago no encontrado'], 404);
            }
            if ($resultado === 'sin_cambio') {
                return response()->json(['message' => 'El pago ya tiene ese método de pago.'], 422);
            }

            return response()->json([
                'success' => true,
                'mensaje' => 'Método de pago actualizado y registrado en la bitácora exitosamente.',
            ]);
        } catch (\Throwable $e) {
            return $this->errorInterno($e);
        }
    }

    /** Registra el detalle en el log y devuelve un mensaje genérico al cliente. */
    private function errorInterno(\Throwable $e)
    {
        Log::error($e->getMessage(), ['exception' => $e]);
        return response()->json(['message' => 'Error interno del servidor.'], 500);
    }
}