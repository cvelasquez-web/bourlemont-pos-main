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
}