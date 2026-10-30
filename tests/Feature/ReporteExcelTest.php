<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Enums\RolUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ReporteExcelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_descargar_excel()
    {
        $admin = Usuario::create([
            'nombre' => 'Admin Test',
            'rol' => RolUsuario::ADMIN,
            'activo' => true,
            'pin_hash' => Usuario::generarHash('1111'),
        ]);
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/reportes/ventas/excel');

        // Verifica que la respuesta sea exitosa y tenga el header de Excel
        $response->assertStatus(200);
        
        // Validamos que el header de descarga esté presente
        $this->assertStringContainsString(
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            $response->headers->get('content-type')
        );
    }
}