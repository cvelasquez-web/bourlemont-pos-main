<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Models\Orden;
use App\Enums\RolUsuario;
use App\Enums\EstadoOrden;
use App\Services\EscandalloService;
use Illuminate\Foundation\Testing\RefreshDatabase; // <-- CAMBIO 1
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrdenCobroTest extends TestCase
{
    use RefreshDatabase; // <-- CAMBIO 2

    protected function setUp(): void
    {
        parent::setUp();
        
        // Simulamos el servicio de escandallo para que no afecte el inventario real
        $this->mock(EscandalloService::class, function ($mock) {
            $mock->shouldReceive('deducirInventario')->andReturn([]);
        });
    }

    public function test_cobro_exitoso_con_efectivo()
    {
        $usuario = Usuario::create([
            'nombre' => 'Cajero Test',
            'rol' => RolUsuario::BARTENDER,
            'activo' => true,
            'pin_hash' => Hash::make('1234'),
        ]);

        Sanctum::actingAs($usuario);

        $orden = Orden::create([
            'numero_mesa' => 'Mesa 1',
            'id_usuario_creacion' => $usuario->id_usuario,
            'estado' => EstadoOrden::ABIERTA,
            'total' => 100.00,
            'fecha_creacion' => now(),
        ]);

        $response = $this->postJson("/api/v1/ordenes/{$orden->id_orden}/cobrar", [
            'metodo_pago' => 'EFECTIVO',
            'monto_pagado' => 100.00,
            'propina' => 0
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        $this->assertDatabaseHas('ordenes', [
            'id_orden' => $orden->id_orden,
            'estado' => EstadoOrden::PAGADA->value,
        ]);

        $this->assertDatabaseHas('transacciones_pago', [
            'id_orden' => $orden->id_orden,
            'metodo_pago' => 'EFECTIVO',
            'monto_pagado' => 100.00,
        ]);
    }

    public function test_cobro_orden_inexistente()
    {
        $usuario = Usuario::create([
            'nombre' => 'Cajero Test',
            'rol' => RolUsuario::BARTENDER,
            'activo' => true,
            'pin_hash' => Hash::make('1234'),
        ]);

        Sanctum::actingAs($usuario);

        $response = $this->postJson("/api/v1/ordenes/999999/cobrar", [
            'metodo_pago' => 'EFECTIVO',
            'monto_pagado' => 100.00,
        ]);

        $response->assertStatus(404)
                 ->assertJson(['detail' => 'Orden no encontrada.']);
    }

    public function test_cobro_orden_ya_pagada()
    {
        $usuario = Usuario::create([
            'nombre' => 'Cajero Test',
            'rol' => RolUsuario::BARTENDER,
            'activo' => true,
            'pin_hash' => Hash::make('1234'),
        ]);

        Sanctum::actingAs($usuario);

        $orden = Orden::create([
            'numero_mesa' => 'Mesa 2',
            'id_usuario_creacion' => $usuario->id_usuario,
            'estado' => EstadoOrden::PAGADA, // Ya está pagada
            'total' => 100.00,
            'fecha_creacion' => now(),
        ]);

        $response = $this->postJson("/api/v1/ordenes/{$orden->id_orden}/cobrar", [
            'metodo_pago' => 'EFECTIVO',
            'monto_pagado' => 100.00,
        ]);

        $response->assertStatus(409)
                 ->assertJson(['detail' => 'La orden ya se encuentra pagada o anulada.']);
    }

    public function test_cobro_monto_insuficiente()
    {
        $usuario = Usuario::create([
            'nombre' => 'Cajero Test',
            'rol' => RolUsuario::BARTENDER,
            'activo' => true,
            'pin_hash' => Hash::make('1234'),
        ]);

        Sanctum::actingAs($usuario);

        $orden = Orden::create([
            'numero_mesa' => 'Mesa 3',
            'id_usuario_creacion' => $usuario->id_usuario,
            'estado' => EstadoOrden::ABIERTA,
            'total' => 100.00,
            'fecha_creacion' => now(),
        ]);

        $response = $this->postJson("/api/v1/ordenes/{$orden->id_orden}/cobrar", [
            'metodo_pago' => 'EFECTIVO',
            'monto_pagado' => 50.00, // Menos del total
        ]);

        $response->assertStatus(422)
                 ->assertJson(['detail' => 'El monto pagado es menor al total de la orden.']);
    }

    public function test_cobro_metodo_pago_invalido()
    {
        $usuario = Usuario::create([
            'nombre' => 'Cajero Test',
            'rol' => RolUsuario::BARTENDER,
            'activo' => true,
            'pin_hash' => Hash::make('1234'),
        ]);

        Sanctum::actingAs($usuario);

        $orden = Orden::create([
            'numero_mesa' => 'Mesa 4',
            'id_usuario_creacion' => $usuario->id_usuario,
            'estado' => EstadoOrden::ABIERTA,
            'total' => 100.00,
            'fecha_creacion' => now(),
        ]);

        $response = $this->postJson("/api/v1/ordenes/{$orden->id_orden}/cobrar", [
            'metodo_pago' => 'BITCOIN', // No está en el Enum MetodoPago
            'monto_pagado' => 100.00,
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['metodo_pago']);
    }
}