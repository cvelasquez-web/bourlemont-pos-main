<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Enums\RolUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase; // <-- CAMBIO 1
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SeguridadTest extends TestCase
{
    use RefreshDatabase; // <-- CAMBIO 2

    public function test_cobro_sin_autenticacion()
    {
        // Hacemos la petición sin usar Sanctum::actingAs
        $response = $this->postJson("/api/v1/ordenes/1/cobrar", [
            'metodo_pago' => 'EFECTIVO',
            'monto_pagado' => 100.00,
        ]);

        // El middleware auth:sanctum debe bloquearlo
        $response->assertStatus(401);
    }

    public function test_ruta_admin_bloqueada_para_bartender()
    {
        $bartender = Usuario::create([
            'nombre' => 'Bartender Test',
            'rol' => RolUsuario::BARTENDER, // Rol sin permisos de admin
            'activo' => true,
            'pin_hash' => Hash::make('1234'),
        ]);

        Sanctum::actingAs($bartender);

        // Intentamos acceder a una ruta protegida por el middleware rol:ADMIN
        $response = $this->postJson('/api/v1/usuarios', [
            'nombre' => 'Nuevo Usuario',
        ]);

        // Tu RolMiddleware debería devolver un 403 (Prohibido)
        $response->assertStatus(403);
    }
}