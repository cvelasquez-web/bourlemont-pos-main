<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use App\Enums\RolUsuario;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

use App\Http\Requests\Api\V1\CrearUsuarioRequest;
use App\Http\Requests\Api\V1\ActualizarUsuarioRequest;

class UsuarioController extends Controller
{
    public function listar()
    {
        return response()->json(Usuario::all());
    }

    public function crear(CrearUsuarioRequest $request)
    {
        $this->validarPinLibre($request->pin);

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'rol' => $request->rol,
            'foto_url' => $request->foto_url,
            'pin_hash' => Usuario::generarHash($request->pin),
            'password_hash' => $request->password ? Usuario::generarHash($request->password) : null,
            'activo' => true
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Usuario creado exitosamente.',
            'usuario' => $usuario
        ], 201);
    }

    public function actualizar(ActualizarUsuarioRequest $request, $id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $esMismoUsuario = $request->user() && $request->user()->id_usuario === $usuario->id_usuario;

        if ($esMismoUsuario && $request->has('activo') && !$request->boolean('activo')) {
            return response()->json(['message' => 'No puedes desactivar tu propia cuenta.'], 422);
        }
        if ($esMismoUsuario && $request->has('rol') && $request->rol !== $usuario->rol->value) {
            return response()->json(['message' => 'No puedes cambiar tu propio rol.'], 422);
        }

        if ($request->has('nombre')) $usuario->nombre = $request->nombre;
        if ($request->has('rol')) $usuario->rol = $request->rol;
        if ($request->has('foto_url')) $usuario->foto_url = $request->foto_url;
        if ($request->has('activo')) $usuario->activo = $request->boolean('activo');

        if ($request->filled('pin')) {
            $this->validarPinLibre($request->pin, $usuario->id_usuario);
            $usuario->pin_hash = Usuario::generarHash($request->pin);
        }
        if ($request->filled('password')) {
            $usuario->password_hash = Usuario::generarHash($request->password);
        }

        $usuario->save();

        return response()->json([
            'success' => true,
            'mensaje' => 'Usuario actualizado exitosamente.',
            'usuario' => $usuario
        ]);
    }

    public function eliminar(Request $request, $id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        if ($request->user()->id_usuario === $usuario->id_usuario) {
            return response()->json(['message' => 'No puedes desactivar tu propia cuenta.'], 422);
        }

        $usuario->activo = false;
        $usuario->save();

        return response()->json([
            'success' => true,
            'mensaje' => 'Usuario desactivado correctamente.'
        ]);
    }

    /**
     * loginPin entra con el primer usuario activo que coincida,
     * así que dos usuarios activos no pueden compartir PIN.
     */
    private function validarPinLibre(string $pin, ?int $ignorarId = null): void
    {
        $query = Usuario::where('activo', true);
        if ($ignorarId) {
            $query->where('id_usuario', '!=', $ignorarId);
        }

        foreach ($query->get() as $u) {
            if ($u->verificarPin($pin)) {
                throw ValidationException::withMessages([
                    'pin' => ['Ese PIN ya está en uso por otro usuario.'],
                ]);
            }
        }
    }
}