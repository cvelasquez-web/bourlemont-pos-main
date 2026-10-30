<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OrdenController;
use App\Http\Controllers\Api\V1\InventarioController;
use App\Http\Controllers\Api\V1\UsuarioController;
use App\Http\Controllers\Api\V1\AuditoriaController;
use App\Http\Controllers\Api\V1\ReporteController;
use App\Http\Controllers\Api\V1\CajaController;

Route::prefix('v1')->group(function () {

    // Rutas Públicas (Login)
    Route::post('/auth/login-pin', [AuthController::class, 'loginPin'])->middleware(['web', 'throttle:' . config('auth.login_throttle') . ',1']);
    Route::post('/auth/login-admin', [AuthController::class, 'loginAdmin'])->middleware(['web', 'throttle:' . config('auth.login_throttle') . ',1']);
    Route::get('/auth/nit/{nit}', [AuthController::class, 'buscarNit']);

    // Rutas Protegidas (Requieren estar logueado)
    Route::middleware('auth:sanctum')->group(function () {

        Route::get('/auth/me', [AuthController::class, 'me']);

        // --- CAJA ---
        Route::get('/caja/actual', [CajaController::class, 'actual']); 
        Route::get('/caja/historial', [CajaController::class, 'historial']); // <-- NUEVA RUTA AGREGADA
        Route::post('/caja/abrir', [CajaController::class, 'abrir']);
        Route::post('/caja/cerrar', [CajaController::class, 'cerrar']);

        // --- ADMIN y BARTENDER ---
        
        // Rutas de Órdenes (El POS y el Dashboard las usan)
        Route::get('/ordenes', [OrdenController::class, 'index']);              
        Route::get('/ordenes/{id}', [OrdenController::class, 'show']);          
        Route::post('/ordenes', [OrdenController::class, 'crear']);             
        Route::put('/ordenes/{id}', [OrdenController::class, 'update']);        
        Route::post('/ordenes/{id}/cobrar', [OrdenController::class, 'cobrar']); 
        Route::post('/ordenes/{id}/anular', [OrdenController::class, 'anular']); 

        // Rutas de Inventario (Solo lectura para Bartenders)
        Route::get('/inventario/productos', [InventarioController::class, 'listarProductos']);
        Route::get('/inventario/insumos', [InventarioController::class, 'listarInsumos']);
        Route::get('/inventario/recetas', [InventarioController::class, 'listarRecetas']);

        // --- Solo ADMIN ---
        Route::middleware('rol:ADMIN')->group(function () {
            // Inventario
            Route::post('/inventario/productos', [InventarioController::class, 'crearProducto']);
            Route::put('/inventario/productos/{id}', [InventarioController::class, 'actualizarProducto']);
            Route::delete('/inventario/productos/{id}', [InventarioController::class, 'eliminarProducto']);
            
            // Subida de imágenes
            Route::post('/inventario/upload-imagen', [InventarioController::class, 'uploadImagen']);

            // Recetas y Escandallos
            Route::post('/inventario/recetas', [InventarioController::class, 'asignarReceta']);
            Route::post('/inventario/recetas/ingrediente', [InventarioController::class, 'agregarIngrediente']);
            Route::put('/inventario/recetas/{id}', [InventarioController::class, 'actualizarReceta']);
            Route::delete('/inventario/recetas/{id}', [InventarioController::class, 'eliminarReceta']);

            // Usuarios
            Route::get('/usuarios', [UsuarioController::class, 'listar']);
            Route::post('/usuarios', [UsuarioController::class, 'crear']);
            Route::put('/usuarios/{id}', [UsuarioController::class, 'actualizar']);
            Route::delete('/usuarios/{id}', [UsuarioController::class, 'eliminar']);
            
            // Subida de avatar
            Route::post('/usuarios/upload-avatar', [UsuarioController::class, 'uploadAvatar']);

            // Auditoría
            Route::get('/auditoria/pagos', [AuditoriaController::class, 'listarPagos']);
            Route::get('/auditoria/logs', [AuditoriaController::class, 'listarLogs']);
            Route::post('/auditoria/cambiar-metodo-pago', [AuditoriaController::class, 'cambiarMetodoPago']);

            // Reportes
            Route::get('/reportes/dashboard', [ReporteController::class, 'dashboardMetrics']);
            Route::get('/reportes/ventas/excel', [ReporteController::class, 'descargarExcel']);
        });
    });
});