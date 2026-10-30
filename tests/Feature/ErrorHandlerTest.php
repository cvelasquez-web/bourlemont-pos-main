<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\Producto;
use App\Enums\RolUsuario;
use App\Enums\CategoriaProducto;
use App\Enums\TipoProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\DB;

class ErrorHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_error_500_controlado_en_creacion_de_orden()
    {
        $bartender = Usuario::create([
            'nombre' => 'Bartender Test',
            'rol' => RolUsuario::BARTENDER,
            'activo' => true,
            'pin_hash' => Usuario::generarHash('1111'),
        ]);
        Sanctum::actingAs($bartender);

        // 1. Creamos un producto válido para que la validación pase
        $producto = Producto::create([
            'nombre' => 'Ron Test',
            'categoria' => CategoriaProducto::LICORES,
            'tipo' => TipoProducto::INSUMO,
            'precio_venta' => 100,
            'stock_ml' => 750,
            'stock_minimo_ml' => 100,
            'unidad_medida' => 'ml',
        ]);

        // 2. 🚨 FORZAMOS EL ERROR: Renombramos la tabla 'detalle_orden' para que falle al insertar
        DB::statement('ALTER TABLE detalle_orden RENAME TO detalle_orden_temporal');

        $response = $this->postJson('/api/v1/ordenes', [
            'numero_mesa' => 'Mesa 1',
            'items' => [
                ['id_producto' => $producto->id_producto, 'cantidad' => 1]
            ]
        ]);

        // 3. Volvemos a dejar la tabla en su lugar
        DB::statement('ALTER TABLE detalle_orden_temporal RENAME TO detalle_orden');

        // 4. Verificamos que el cliente NO vea el error SQL, sino nuestro JSON 500
        $response->assertStatus(500)
                 ->assertJsonStructure(['detail'])
                 ->assertJsonFragment(['detail' => 'Error al crear la orden.']);
    }
}