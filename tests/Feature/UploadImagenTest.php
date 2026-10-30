<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\Producto;
use App\Enums\RolUsuario;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class UploadImagenTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_subir_imagen_de_producto()
    {
        // 1. Simulamos el disco público
        Storage::fake('public');

        // 2. Creamos un admin y lo autenticamos
        $admin = Usuario::create([
            'nombre' => 'Admin Test',
            'rol' => RolUsuario::ADMIN,
            'activo' => true,
            'pin_hash' => Usuario::generarHash('1111'),
        ]);
        Sanctum::actingAs($admin);

        // 3. Simulamos el archivo de imagen
        $archivo = UploadedFile::fake()->image('hennessy.jpg', 600, 600);

        // 4. Hacemos la petición POST
        $response = $this->postJson('/api/v1/inventario/upload-imagen', [
            'file' => $archivo
        ]);

        // 5. Verificamos la respuesta
        $response->assertStatus(200)
                 ->assertJsonStructure(['url', 'filename']);

        // 6. Verificamos que el archivo realmente se guardó en la carpeta "productos"
        Storage::disk('public')->assertExists('productos/' . $archivo->hashName());
    }
}