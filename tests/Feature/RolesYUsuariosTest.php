<?php

namespace Tests\Feature;

use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RolesYUsuariosTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(string $rol, string $pin): Usuario
    {
        return Usuario::create([
            'nombre' => 'Test ' . $rol . ' ' . $pin,
            'rol' => $rol,
            'pin_hash' => Usuario::generarHash($pin),
            'password_hash' => null,
            'activo' => true,
        ]);
    }

    public function test_bartender_no_puede_leer_rutas_de_admin(): void
    {
        Sanctum::actingAs($this->crearUsuario('BARTENDER', '8301'));

        foreach (['usuarios', 'auditoria/pagos', 'auditoria/logs', 'reportes/dashboard'] as $ruta) {
            $this->getJson("/api/v1/$ruta")->assertStatus(403);
        }
    }

    public function test_bartender_no_puede_escribir_en_rutas_de_admin(): void
    {
        Sanctum::actingAs($this->crearUsuario('BARTENDER', '8302'));

        $this->postJson('/api/v1/usuarios', [])->assertStatus(403);
        $this->postJson('/api/v1/inventario/productos', [])->assertStatus(403);
        $this->deleteJson('/api/v1/inventario/productos/1')->assertStatus(403);
    }

    public function test_bartender_si_puede_listar_inventario(): void
    {
        Sanctum::actingAs($this->crearUsuario('BARTENDER', '8303'));

        $this->getJson('/api/v1/inventario/productos')->assertOk();
    }

    public function test_admin_puede_listar_usuarios_y_no_ve_hashes(): void
    {
        Sanctum::actingAs($this->crearUsuario('ADMIN', '8304'));

        $this->getJson('/api/v1/usuarios')
            ->assertOk()
            ->assertJsonMissing(['pin_hash'])
            ->assertJsonMissing(['password_hash']);
    }

    public function test_no_se_puede_crear_usuario_con_pin_repetido(): void
    {
        Sanctum::actingAs($this->crearUsuario('ADMIN', '8305'));
        $this->crearUsuario('BARTENDER', '8306');

        $this->postJson('/api/v1/usuarios', [
            'nombre' => 'Duplicado',
            'rol' => 'BARTENDER',
            'pin' => '8306',
        ])->assertStatus(422)->assertJsonValidationErrors('pin');
    }

    public function test_rol_invalido_da_422(): void
    {
        Sanctum::actingAs($this->crearUsuario('ADMIN', '8307'));

        $this->postJson('/api/v1/usuarios', [
            'nombre' => 'Rol malo',
            'rol' => 'SUPERUSUARIO',
            'pin' => '8308',
        ])->assertStatus(422);
    }

    public function test_admin_no_puede_desactivarse_a_si_mismo(): void
    {
        $admin = $this->crearUsuario('ADMIN', '8309');
        Sanctum::actingAs($admin);

        $this->deleteJson('/api/v1/usuarios/' . $admin->id_usuario)->assertStatus(422);
        $this->assertTrue($admin->fresh()->activo);
    }

    public function test_cambiar_metodo_pago_invalido_da_422(): void
    {
        Sanctum::actingAs($this->crearUsuario('ADMIN', '8310'));

        $this->postJson('/api/v1/auditoria/cambiar-metodo-pago', [
            'id_pago' => 1,
            'nuevo_metodo_pago' => 'BITCOIN',
            'motivo' => 'prueba de validacion',
        ])->assertStatus(422);
    }

    public function test_peticion_sin_token_da_401(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }
}