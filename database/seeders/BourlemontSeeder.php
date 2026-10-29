<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Producto;
use App\Models\RecetaEscandallo;
use App\Models\ArqueoCaja;
use App\Models\BitacoraAuditoria;
use App\Enums\RolUsuario;
use App\Enums\CategoriaProducto;
use App\Enums\TipoProducto;
use App\Enums\EstadoArqueo;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class BourlemontSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now('America/Guatemala');

        // ── 1. USUARIOS ────────────────────────────────────────────────────────
        $mateo = Usuario::updateOrCreate(
            ['nombre' => 'Mateo Morales'],
            [
                'pin_hash' => Hash::make('1234'),
                'rol' => RolUsuario::BARTENDER,
                'activo' => true,
            ]
        );
        
        $carlos = Usuario::updateOrCreate(
            ['nombre' => 'Carlos Bourlemont'],
            [
                'pin_hash' => Hash::make('2026'),
                'password_hash' => Hash::make('admin'),
                'rol' => RolUsuario::ADMIN,
                'activo' => true,
            ]
        );

        $sofia = Usuario::updateOrCreate(
            ['nombre' => 'Sofía Castillo'],
            [
                'pin_hash' => Hash::make('2468'),
                'rol' => RolUsuario::BARTENDER,
                'activo' => true,
            ]
        );

        $alejandro = Usuario::updateOrCreate(
            ['nombre' => 'Alejandro Ruiz'],
            [
                'pin_hash' => Hash::make('7788'),
                'rol' => RolUsuario::BARTENDER,
                'activo' => true,
            ]
        );

        // ── 2. INSUMOS BASE ───────────────────────────────────────────────────
        $champagne_brut = Producto::updateOrCreate(
            ['nombre' => 'Champagne Brut (750 ml)'],
            [
                'categoria' => CategoriaProducto::INSUMOS_BASE,
                'tipo' => TipoProducto::INSUMO,
                'precio_venta' => 0.00,
                'stock_ml' => 7500,
                'stock_minimo_ml' => 750,
                'unidad_medida' => 'ml',
                'imagen_url' => '/assets/img/champagne-brut.jpg'
            ]
        );

        $gin_tanqueray = Producto::updateOrCreate(
            ['nombre' => 'Gin Tanqueray (750 ml)'],
            [
                'categoria' => CategoriaProducto::LICORES,
                'tipo' => TipoProducto::INSUMO,
                'precio_venta' => 650.00,
                'stock_ml' => 7500,
                'stock_minimo_ml' => 750,
                'unidad_medida' => 'ml',
                'imagen_url' => '/assets/img/tanqueray.jpg'
            ]
        );

        $cognac_hennessy = Producto::updateOrCreate(
            ['nombre' => 'Cognac Hennessy (700 ml)'],
            [
                'categoria' => CategoriaProducto::LICORES,
                'tipo' => TipoProducto::INSUMO,
                'precio_venta' => 2600.00,
                'stock_ml' => 4900,
                'stock_minimo_ml' => 700,
                'unidad_medida' => 'ml',
                'imagen_url' => '/assets/img/hennessy.jpg'
            ]
        );

        $jarabe = Producto::updateOrCreate(
            ['nombre' => 'Jarabe/Sirope (1000 ml)'],
            [
                'categoria' => CategoriaProducto::INSUMOS_BASE,
                'tipo' => TipoProducto::INSUMO,
                'precio_venta' => 0.00,
                'stock_ml' => 5000,
                'stock_minimo_ml' => 1000,
                'unidad_medida' => 'ml',
                'imagen_url' => '/assets/img/sirope.jpg'
            ]
        );

        $dom_vintage = Producto::updateOrCreate(
            ['nombre' => 'Dom Pérignon Vintage 2013'],
            [
                'categoria' => CategoriaProducto::CHAMPAGNE,
                'tipo' => TipoProducto::INSUMO,
                'precio_venta' => 3800.00,
                'stock_ml' => 7500,
                'stock_minimo_ml' => 1500,
                'unidad_medida' => 'ml',
                'imagen_url' => '/assets/img/dom-perignon.jpg'
            ]
        );

        $tanqueray_ten = Producto::updateOrCreate(
            ['nombre' => 'Gin Tanqueray No. TEN'],
            [
                'categoria' => CategoriaProducto::LICORES,
                'tipo' => TipoProducto::INSUMO,
                'precio_venta' => 650.00,
                'stock_ml' => 7500,
                'stock_minimo_ml' => 1500,
                'unidad_medida' => 'ml'
            ]
        );

        $moet = Producto::updateOrCreate(
            ['nombre' => 'Moët & Chandon Impérial Brut'],
            [
                'categoria' => CategoriaProducto::CHAMPAGNE,
                'tipo' => TipoProducto::INSUMO,
                'precio_venta' => 1200.00,
                'stock_ml' => 15000,
                'stock_minimo_ml' => 3000,
                'unidad_medida' => 'ml'
            ]
        );

        $limon = Producto::updateOrCreate(
            ['nombre' => 'Jugo de Limón Fresco'],
            [
                'categoria' => CategoriaProducto::INSUMOS_BASE,
                'tipo' => TipoProducto::INSUMO,
                'precio_venta' => 0.00,
                'stock_ml' => 3000,
                'stock_minimo_ml' => 800,
                'unidad_medida' => 'ml'
            ]
        );

        // ── 3. PRODUCTOS FINALES / CÓCTELES ────────────────────────────────────
        $copa_champagne = Producto::updateOrCreate(
            ['nombre' => 'Copa Champagne Brut (150 ml)'],
            [
                'categoria' => CategoriaProducto::CHAMPAGNE,
                'tipo' => TipoProducto::COMPUESTO,
                'precio_venta' => 260.00,
                'stock_ml' => 0,
                'stock_minimo_ml' => 0,
                'unidad_medida' => 'copa',
                'imagen_url' => '/assets/img/copa-champagne.jpg'
            ]
        );

        $french_75 = Producto::updateOrCreate(
            ['nombre' => 'French 75 Bourlemont'],
            [
                'categoria' => CategoriaProducto::COCTELES_AUTOR,
                'tipo' => TipoProducto::COMPUESTO,
                'precio_venta' => 185.00,
                'stock_ml' => 0,
                'stock_minimo_ml' => 0,
                'unidad_medida' => 'copa',
                'imagen_url' => '/assets/img/french-75.jpg'
            ]
        );

        $dom_perignon_coctel = Producto::updateOrCreate(
            ['nombre' => 'Dom Pérignon Vintage'],
            [
                'categoria' => CategoriaProducto::CHAMPAGNE,
                'tipo' => TipoProducto::COMPUESTO,
                'precio_venta' => 3800.00,
                'stock_ml' => 0,
                'stock_minimo_ml' => 0,
                'unidad_medida' => 'botella',
                'imagen_url' => '/assets/img/dom-perignon.jpg'
            ]
        );

        // ── 4. RECETAS / ESCANDALLOS ──────────────────────────────────────────
        // Copa Champagne
        RecetaEscandallo::updateOrCreate(
            ['id_producto_final' => $copa_champagne->id_producto, 'id_insumo_base' => $champagne_brut->id_producto],
            ['cantidad_descontar_ml' => 150]
        );

        // French 75 (3 ingredientes)
        RecetaEscandallo::updateOrCreate(
            ['id_producto_final' => $french_75->id_producto, 'id_insumo_base' => $champagne_brut->id_producto],
            ['cantidad_descontar_ml' => 90]
        );
        RecetaEscandallo::updateOrCreate(
            ['id_producto_final' => $french_75->id_producto, 'id_insumo_base' => $gin_tanqueray->id_producto],
            ['cantidad_descontar_ml' => 45]
        );
        RecetaEscandallo::updateOrCreate(
            ['id_producto_final' => $french_75->id_producto, 'id_insumo_base' => $jarabe->id_producto],
            ['cantidad_descontar_ml' => 15]
        );

        // Dom Pérignon
        RecetaEscandallo::updateOrCreate(
            ['id_producto_final' => $dom_perignon_coctel->id_producto, 'id_insumo_base' => $dom_vintage->id_producto],
            ['cantidad_descontar_ml' => 750]
        );

        // ── 5. TURNO DE CAJA & BITÁCORA ────────────────────────────────────────
        // Nota: El arqueo se actualiza buscando si ya hay uno abierto para Mateo hoy
        ArqueoCaja::updateOrCreate(
            [
                'id_usuario' => $mateo->id_usuario,
                'fecha_apertura' => $now->copy()->subHours(2)->startOfMinute(), // Usamos startOfMinute para evitar problemas de microsegundos
            ],
            [
                'fondo_inicial' => 1000.00,
                'estado' => EstadoArqueo::ABIERTO
            ]
        );

        BitacoraAuditoria::create([
            'id_usuario' => $carlos->id_usuario,
            'accion' => 'INICIALIZACION_FASE_1',
            'descripcion' => 'Tablas regeneradas y datos semilla iniciales cargados en MySQL.'
        ]);

        $this->command->info('¡Base de datos poblada exitosamente con los datos de Bourlemont POS!');
    }
}