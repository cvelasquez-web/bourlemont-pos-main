<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    public function actual()
    {
        // Aquí debes retornar el estado actual de la caja
        // Por ejemplo, si hay una caja abierta:
        return response()->json([
            'estado' => 'abierta', // o 'cerrada'
            'monto_inicial' => 0,
            'monto_actual' => 0,
        ]);
    }

    public function historial()
    {
        // Aquí deberías retornar el historial de cajas (ej. cajas cerradas)
        // Por ahora, retornamos un array vacío para que no falle
        return response()->json([]);
    }
}