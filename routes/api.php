<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrdenController;
use App\Http\Controllers\Api\V1\InventarioController;
use App\Http\Controllers\Api\V1\UsuarioController;
use App\Http\Controllers\Api\V1\AuditoriaController;
use App\Http\Controllers\Api\V1\ReporteController;

Route::prefix('v1')->group(function () {

    // Rutas Públicas (Login)
    Route::post('/auth/login-pin', [AuthController::class, 'loginPin'])->middleware('throttle:5,1');
    Route::post('/auth/login-admin', [AuthController::class, 'loginAdmin'])->middleware('throttle:5,1');
    Route::get('/auth/nit/{nit}', [AuthController::class, 'buscarNit']);

    // Rutas Protegidas (Requieren estar logueado)
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/auth/me', [AuthController::class, 'me']);

        // --- ADMIN y BARTENDER ---
        Route::post('/ordenes', [OrdenController::class, 'crear']);
        Route::post('/ordenes/{id_orden}/cobrar', [OrdenController::class, 'cobrar']);
        Route::get('/inventario/productos', [InventarioController::class, 'listarProductos']);
        Route::get('/inventario/insumos', [InventarioController::class, 'listarInsumos']);
        Route::get('/inventario/recetas', [InventarioController::class, 'listarRecetas']);

        // --- Solo ADMIN ---
        Route::middleware('rol:ADMIN')->group(function () {
            Route::post('/inventario/productos', [InventarioController::class, 'crearProducto']);
            Route::put('/inventario/productos/{id}', [InventarioController::class, 'actualizarProducto']);
            Route::delete('/inventario/productos/{id}', [InventarioController::class, 'eliminarProducto']);

            Route::post('/inventario/recetas', [InventarioController::class, 'asignarReceta']);
            Route::post('/inventario/recetas/ingrediente', [InventarioController::class, 'agregarIngrediente']);
            Route::put('/inventario/recetas/{id}', [InventarioController::class, 'actualizarReceta']);
            Route::delete('/inventario/recetas/{id}', [InventarioController::class, 'eliminarReceta']);

            Route::get('/usuarios', [UsuarioController::class, 'listar']);
            Route::post('/usuarios', [UsuarioController::class, 'crear']);
            Route::put('/usuarios/{id}', [UsuarioController::class, 'actualizar']);
            Route::delete('/usuarios/{id}', [UsuarioController::class, 'eliminar']);

            Route::get('/auditoria/pagos', [AuditoriaController::class, 'listarPagos']);
            Route::get('/auditoria/logs', [AuditoriaController::class, 'listarLogs']);
            Route::post('/auditoria/cambiar-metodo-pago', [AuditoriaController::class, 'cambiarMetodoPago']);

            Route::get('/reportes/dashboard', [ReporteController::class, 'dashboardMetrics']);
            Route::get('/reportes/ventas/excel', [ReporteController::class, 'descargarExcel']);
        });
    });
});