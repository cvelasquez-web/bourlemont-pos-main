<?php

namespace App\Services;

use App\Models\Usuario;
use Carbon\Carbon;

class TicketService
{
    /**
     * Genera el texto formateado para la impresora térmica de 80mm (42 columnas)
     */
    public function generarTicketTermico($orden, $pago)
    {
        $ancho = 42;
        $linea = str_repeat("=", $ancho);
        $lineaDelgada = str_repeat("-", $ancho);
        $fecha = Carbon::now('America/Guatemala')->format('d/m/Y H:i:s');

        $usuario = Usuario::find($orden->id_usuario_creacion);
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