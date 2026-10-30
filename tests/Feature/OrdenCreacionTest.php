<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\Producto;
use App\Models\Orden;
use App\Enums\RolUsuario;
use App\Enums\CategoriaProducto;
use App\Enums\TipoProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrdenCreacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_crear_orden_exitosamente()
    {
        $usuario = Usuario::create([
            'nombre' => 'Bartender Test',
            'rol' => RolUsuario::BARTENDER,
            'activo' => true,
            'pin_hash' => Hash::make('1234'),
        ]);

        Sanctum::actingAs($usuario);

        // Creamos 2 productos en la base de datos de prueba
        $producto1 = Producto::create([
            'nombre' => 'Copa Champagne',
            'categoria' => CategoriaProducto::CHAMPAGNE,
            'tipo' => TipoProducto::COMPUESTO,
            'precio_venta' => 260.00,
            'stock_ml' => 0,
            'stock_minimo_ml' => 0,
            'unidad_medida' => 'copa',
        ]);

        $producto2 = Producto::create([
            'nombre' => 'Gin Tonic',
            'categoria' => CategoriaProducto::COCTELES_AUTOR,
            'tipo' => TipoProducto::COMPUESTO,
            'precio_venta' => 150.00,
            'stock_ml' => 0,
            'stock_minimo_ml' => 0,
            'unidad_medida' => 'copa',
        ]);

        // Hacemos la petición al endpoint de crear orden
        $response = $this->postJson('/api/v1/ordenes', [
            'numero_mesa' => 'Mesa 5',
            'items' => [
                ['id_producto' => $producto1->id_producto, 'cantidad' => 2], // 2 * 260 = 520
                ['id_producto' => $producto2->id_producto, 'cantidad' => 1], // 1 * 150 = 150
            ]
        ]);

        // El total esperado es 520 + 150 = 670.00
        $response->assertStatus(201)
                 ->assertJsonPath('numero_mesa', 'Mesa 5')
                 ->assertJsonPath('total', '670.00');

        // Verificamos que la orden se guardó en la base de datos
        $this->assertDatabaseHas('ordenes', [
            'numero_mesa' => 'Mesa 5',
            'total' => 670.00,
        ]);
    }
}