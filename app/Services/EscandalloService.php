<?php

namespace App\Services;

use App\Models\Orden;
use App\Models\Producto;
use App\Models\RecetaEscandallo;

class EscandalloService
{
    /**
     * Si es true, el cobro se rechaza cuando algún insumo no alcanza.
     * Si es false (comportamiento actual), se permite y se avisa con una alerta.
     */
    private const RECHAZAR_SIN_STOCK = false;

    /**
     * Debe llamarse dentro de una transacción (OrdenController::cobrar ya lo hace).
     */
    public function deducirInventario(Orden $orden): array
    {
        $items = $orden->items()->get();
        $productos = Producto::whereIn('id_producto', $items->pluck('id_producto'))->get()->keyBy('id_producto');
        $recetas = RecetaEscandallo::whereIn('id_producto_final', $items->pluck('id_producto'))
            ->get()
            ->groupBy('id_producto_final');

        // 1. Acumular cuántos ml hay que descontar por producto
        $descuentos = [];
        foreach ($items as $item) {
            $vendido = $productos[$item->id_producto] ?? null;
            if (!$vendido) {
                continue;
            }

            if (isset($recetas[$vendido->id_producto])) {
                foreach ($recetas[$vendido->id_producto] as $r) {
                    $descuentos[$r->id_insumo_base] =
                        ($descuentos[$r->id_insumo_base] ?? 0) + $item->cantidad * $r->cantidad_descontar_ml;
                }
            } elseif ($vendido->unidad_medida === 'ml') {
                $descuentos[$vendido->id_producto] =
                    ($descuentos[$vendido->id_producto] ?? 0) + $item->cantidad * $this->mlPorUnidadVendida($vendido);
            }
        }

        if (!$descuentos) {
            return [];
        }

        // 2. Bloquear en orden de id (evita deadlocks entre cobros simultáneos)
        ksort($descuentos);
        $bloqueados = Producto::whereIn('id_producto', array_keys($descuentos))
            ->orderBy('id_producto')
            ->lockForUpdate()
            ->get()
            ->keyBy('id_producto');

        // 3. Validar y descontar
        $alertas = [];
        foreach ($descuentos as $idProducto => $ml) {
            $p = $bloqueados[$idProducto] ?? null;
            if (!$p) {
                continue;
            }

            if (self::RECHAZAR_SIN_STOCK && $p->stock_ml < $ml) {
                throw new \DomainException("Stock insuficiente de '{$p->nombre}' ({$p->stock_ml} ml disponibles).", 409);
            }

            $p->stock_ml -= $ml;
            $p->save();

            if ($p->stock_ml < 0) {
                $alertas[] = "¡ALERTA CRÍTICA! Insumo '{$p->nombre}' con stock negativo ({$p->stock_ml} ml).";
            } elseif ($p->stock_ml <= $p->stock_minimo_ml) {
                $alertas[] = "Aviso de reposición: '{$p->nombre}' alcanzó nivel mínimo ({$p->stock_ml} ml).";
            }
        }

        return $alertas;
    }

    /** ml que se descuentan por cada unidad vendida sin receta (misma regla que antes). */
    private function mlPorUnidadVendida(Producto $p): int
    {
        $esBotella = str_contains(strtolower($p->nombre), 'botella')
            || $p->categoria?->value === 'CHAMPAGNE';

        return $esBotella ? 750 : 45;
    }
}