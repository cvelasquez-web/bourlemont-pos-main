<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('v1')->group(function () {

    // Rutas Públicas de Autenticación (Asignadas a Jefferson)
    Route::post('/auth/login-pin', [AuthController::class, 'loginPin'])->middleware('throttle:5,1');
    Route::post('/auth/login-admin', [AuthController::class, 'loginAdmin'])->middleware('throttle:5,1');
    Route::get('/auth/nit/{nit}', [AuthController::class, 'buscarNit']);

    // Rutas Protegidas (Requieren autenticación)
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/auth/me', [AuthController::class, 'me']);

        /*
        |--------------------------------------------------------------------------
        | Rutas de los demás módulos (Se activarán con los controladores de cada integrante)
        |--------------------------------------------------------------------------
        | - Ordenes / Cobros (OrdenController)
        | - Inventario / Recetas (InventarioController)
        | - Gestión de Usuarios (UsuarioController)
        | - Auditoría y Logs (AuditoriaController)
        | - Reportes y Métricas (ReporteController)
        */
    });
});
