<?php

namespace Tests\Feature;

use App\Models\Usuario;
use App\Enums\RolUsuario;
use Illuminate\Foundation\Testing\RefreshDatabase; // <-- CAMBIO 1
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase; // <-- CAMBIO 2

    public function test_login_pin_exitoso()
    {
        $usuario = Usuario::create([
            'nombre' => 'Bartender Test',
            'rol' => RolUsuario::BARTENDER,
            'activo' => true,
            'pin_hash' => Hash::make('1234'),
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login-pin', [
            'pin' => '1234'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'access_token',
                     'token_type',
                     'usuario' => ['id_usuario', 'nombre', 'rol']
                 ]);
    }

    public function test_login_pin_fallido()
    {
        Usuario::create([
            'nombre' => 'Bartender Test',
            'rol' => RolUsuario::BARTENDER,
            'activo' => true,
            'pin_hash' => Hash::make('1234'),
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login-pin', [
            'pin' => '9999' // PIN incorrecto
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['pin']);
    }

    public function test_login_pin_usuario_inactivo()
    {
        Usuario::create([
            'nombre' => 'Bartender Inactivo',
            'rol' => RolUsuario::BARTENDER,
            'activo' => false, // Usuario inactivo
            'pin_hash' => Hash::make('8765'), // PIN único para evitar choques
            'password_hash' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login-pin', [
            'pin' => '8765'
        ]);

        // Debe fallar porque el controlador filtra solo usuarios activos
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['pin']);
    }

    public function test_login_pin_validacion_formato()
    {
        $response = $this->postJson('/api/v1/auth/login-pin', [
            'pin' => '123' // Solo 3 dígitos
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['pin']);
    }

    public function test_login_pin_rate_limiting()
    {
        // Hacemos 5 intentos fallidos (el límite es 5,1)
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/auth/login-pin', ['pin' => '0000']);
        }

        // El sexto intento debe ser bloqueado por el throttle
        $response = $this->postJson('/api/v1/auth/login-pin', ['pin' => '0000']);
        
        $response->assertStatus(429); // Too Many Requests
    }
}