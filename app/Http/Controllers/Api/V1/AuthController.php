<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Enums\RolUsuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginPin(Request $request)
    {
        $request->validate([
            'pin' => 'required|digits:4'
        ]);

        $usuarios = Usuario::where('activo', true)->get();
        $usuarioEncontrado = null;

        foreach ($usuarios as $u) {
            if ($this->hashCoincide($request->pin, $u->pin_hash)) {
                $usuarioEncontrado = $u;
                break;
            }
        }

        if (!$usuarioEncontrado) {
            throw ValidationException::withMessages([
                'pin' => ['PIN incorrecto o usuario inactivo.'],
            ]);
        }

        $token = $usuarioEncontrado
            ->createToken('pos-token', ['*'], now()->addHours(24))
            ->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'usuario' => $usuarioEncontrado
        ]);
    }

    public function loginAdmin(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'password' => 'required|string'
        ]);

        $usuario = Usuario::where('rol', RolUsuario::ADMIN)
            ->where('activo', true)
            ->where('nombre', $request->nombre)
            ->first();

        $isValid = $usuario
            && $usuario->password_hash
            && $this->hashCoincide($request->password, $usuario->password_hash);

        if (!$isValid) {
            throw ValidationException::withMessages([
                'nombre' => ['Credenciales administrativas inválidas.'],
            ]);
        }

        $token = $usuario
            ->createToken('admin-token', ['*'], now()->addHours(24))
            ->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'usuario' => $usuario
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function buscarNit($nit)
    {
        $nit_clean = strtoupper(trim($nit));

        if (in_array($nit_clean, ["CF", "C/F", ""])) {
            return response()->json(["nit" => "CF", "nombre" => "CONSUMIDOR FINAL"]);
        }

        $padron_local = [
            "38129990" => "JEFFERSON ALEXANDER REYES BARRIOS",
            "98765432" => "DESARROLLO LOS FRANCISCANOS, S.A.",
            "12345678" => "CLUB RESIDENCIAL CIUDAD VIEJA, S.A.",
        ];

        if (array_key_exists($nit_clean, $padron_local)) {
            return response()->json(["nit" => $nit_clean, "nombre" => $padron_local[$nit_clean]]);
        }

        return response()->json(["nit" => $nit_clean, "nombre" => "CLIENTE REGISTRADO"]);
    }

    /**
     * Compara un valor en texto plano contra un hash guardado.
     * Soporta el formato de Python (sal$hash, PBKDF2-SHA256) y bcrypt de Laravel.
     */
    private function hashCoincide(string $valor, ?string $almacenado): bool
    {
        if (!$almacenado) {
            return false;
        }

        if (str_contains($almacenado, '$') && !str_starts_with($almacenado, '$2y$')) {
            [$salt, $hash] = explode('$', $almacenado, 2);
            $calculado = hash_pbkdf2('sha256', $valor, $salt, 100000);
            return hash_equals($hash, $calculado);
        }

        try {
            return Hash::check($valor, $almacenado);
        } catch (\RuntimeException $e) {
            // Si el texto guardado en la BD no es un hash válido (ej. 'dummy'),
            // simplemente retornamos false para que falle la validación.
            return false; 
        }
    }
}