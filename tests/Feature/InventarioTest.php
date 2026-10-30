<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Orden;
use App\Models\DetalleOrden;
use App\Models\RecetaEscandallo;
use App\Models\TransaccionPago;
use App\Enums\RolUsuario;
use App\Enums\EstadoOrden;
use App\Enums\CategoriaProducto;
use App\Enums\TipoProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class InventarioTest extends TestCase
{
    use RefreshDatabase;

    private function bartender(): Usuario
    {
        $u = Usuario::create([
            'nombre' => 'Bartender Test',
            'rol' => RolUsuario::BARTENDER,
            'activo' => true,
            'pin_hash' => Hash::make('1234'),
        ]);
        Sanctum::actingAs($u);

        return $u;
    }

    private function insumo(string $nombre, int $stock, int $minimo = 100): Producto
    {
        return Producto::create([
            'nombre' => $nombre,
            'categoria' => CategoriaProducto::INSUMOS_BASE,
            'tipo' => TipoProducto::INSUMO,
            'precio_venta' => 0.00,
            'stock_ml' => $stock,
            'stock_minimo_ml' => $minimo,
            'unidad_medida' => 'ml',
        ]);
    }

    /** $ingredientes: [[insumo, ml], ...] */
    private function coctel(string $nombre, float $precio, array $ingredientes): Producto
    {
        $c = Producto::create([
            'nombre' => $nombre,
            'categoria' => CategoriaProducto::COCTELES_AUTOR,
            'tipo' => TipoProducto::COMPUESTO,
            'precio_venta' => $precio,
            'stock_ml' => 0,
            'stock_minimo_ml' => 0,
            'unidad_medida' => 'copa',
        ]);

        foreach ($ingredientes as [$insumo, $ml]) {
            RecetaEscandallo::create([
                'id_producto_final' => $c->id_producto,
                'id_insumo_base' => $insumo->id_producto,
                'cantidad_descontar_ml' => $ml,
            ]);
        }

        return $c;
    }

    /** $lineas: [[producto, cantidad], ...] */
    private function ordenCon(Usuario $u, array $lineas): Orden
    {
        $total = 0;
        $orden = Orden::create([
            'numero_mesa' => 'Mesa Test',
            'id_usuario_creacion' => $u->id_usuario,
            'estado' => EstadoOrden::ABIERTA,
            'total' => 0,
            'fecha_creacion' => now(),
        ]);

        foreach ($lineas as [$producto, $cantidad]) {
            $subtotal = $producto->precio_venta * $cantidad;
            $total += $subtotal;
            DetalleOrden::create([
                'id_orden' => $orden->id_orden,
                'id_producto' => $producto->id_producto,
                'cantidad' => $cantidad,
                'precio_unitario' => $producto->precio_venta,
                'subtotal' => $subtotal,
            ]);
        }

        $orden->update(['total' => $total]);

        return $orden;
    }

    private function cobrar(Orden $orden, float $monto)
    {
        return $this->postJson("/api/v1/ordenes/{$orden->id_orden}/cobrar", [
            'metodo_pago' => 'TARJETA_VISA',
            'monto_pagado' => $monto,
        ]);
    }

    private function stock(Producto $p): int
    {
        return (int) Producto::find($p->id_producto)->stock_ml;
    }

    public function test_deduccion_de_inventario_al_cobrar()
    {
        $usuario = $this->bartender();

        $champagne = $this->insumo('Champagne Brut', 1000);
        $gin = $this->insumo('Gin Tanqueray', 1000);
        $french75 = $this->coctel('French 75', 185.00, [[$champagne, 90], [$gin, 45]]);

        $orden = $this->ordenCon($usuario, [[$french75, 2]]);

        $this->cobrar($orden, 370.00)->assertStatus(200);

        // Champagne: 1000 - (2 * 90) = 820. Gin: 1000 - (2 * 45) = 910.
        $this->assertDatabaseHas('productos', ['id_producto' => $champagne->id_producto, 'stock_ml' => 820]);
        $this->assertDatabaseHas('productos', ['id_producto' => $gin->id_producto, 'stock_ml' => 910]);
    }

    public function test_dos_cocteles_que_comparten_insumo_suman_el_descuento()
    {
        $usuario = $this->bartender();

        $gin = $this->insumo('Gin Compartido', 1000);
        $limon = $this->insumo('Limon', 500);
        $a = $this->coctel('Coctel A', 50.00, [[$gin, 45], [$limon, 20]]);
        $b = $this->coctel('Coctel B', 60.00, [[$gin, 60]]);

        $orden = $this->ordenCon($usuario, [[$a, 2], [$b, 1]]);

        $this->cobrar($orden, 160.00)->assertOk();

        // gin: 2*45 + 1*60 = 150 ; limon: 2*20 = 40
        $this->assertSame(850, $this->stock($gin));
        $this->assertSame(460, $this->stock($limon));
    }

    public function test_cobro_con_stock_insuficiente_permite_y_deja_alerta()
    {
        $usuario = $this->bartender();

        $gin = $this->insumo('Gin Escaso', 30);
        $coctel = $this->coctel('Coctel Escaso', 50.00, [[$gin, 45]]);
        $orden = $this->ordenCon($usuario, [[$coctel, 1]]);

        $resp = $this->cobrar($orden, 50.00)->assertOk();

        // Comportamiento actual: se cobra, el stock queda negativo y hay alerta.
        $this->assertSame(-15, $this->stock($gin));
        $this->assertNotEmpty($resp->json('alertas_inventario'));
    }

    public function test_alerta_de_reposicion_al_llegar_al_minimo()
    {
        $usuario = $this->bartender();

        $gin = $this->insumo('Gin Minimo', 150, 120);
        $coctel = $this->coctel('Coctel Minimo', 50.00, [[$gin, 45]]);
        $orden = $this->ordenCon($usuario, [[$coctel, 1]]);

        $resp = $this->cobrar($orden, 50.00)->assertOk();

        $this->assertSame(105, $this->stock($gin));
        $this->assertNotEmpty($resp->json('alertas_inventario'));
    }

    public function test_monto_insuficiente_no_descuenta_stock_ni_registra_pago()
    {
        $usuario = $this->bartender();

        $gin = $this->insumo('Gin Intacto', 1000);
        $coctel = $this->coctel('Coctel Intacto', 50.00, [[$gin, 45]]);
        $orden = $this->ordenCon($usuario, [[$coctel, 1]]);

        $this->cobrar($orden, 1.00)->assertStatus(422);

        $this->assertSame(1000, $this->stock($gin));
        $this->assertSame(0, TransaccionPago::where('id_orden', $orden->id_orden)->count());
    }

    public function test_cobrar_dos_veces_no_descuenta_dos_veces()
    {
        $usuario = $this->bartender();

        $gin = $this->insumo('Gin Una Vez', 1000);
        $coctel = $this->coctel('Coctel Una Vez', 50.00, [[$gin, 45]]);
        $orden = $this->ordenCon($usuario, [[$coctel, 1]]);

        $this->cobrar($orden, 50.00)->assertOk();
        $this->cobrar($orden, 50.00)->assertStatus(409);

        $this->assertSame(955, $this->stock($gin));
        $this->assertSame(1, TransaccionPago::where('id_orden', $orden->id_orden)->count());
    }
}