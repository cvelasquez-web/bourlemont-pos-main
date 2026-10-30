<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreUsuarioRequest;
use App\Http\Requests\Api\V1\UpdateUsuarioRequest;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UsuarioController extends Controller
{
    public function listar()
    {
        // Ocultamos los hashes por seguridad
        return response()->json(Usuario::all()->makeHidden(['pin_hash', 'password_hash']));
    }

    public function crear(StoreUsuarioRequest $request)
    {
        $datos = $request->validated();

        $this->validarPinLibre($datos['pin']);

        $usuario = Usuario::create([
            'nombre' => $datos['nombre'],
            'rol' => $datos['rol'],
            'pin_hash' => Usuario::generarHash($datos['pin']),
            'password_hash' => !empty($datos['password']) ? Usuario::generarHash($datos['password']) : null,
            'foto_url' => $datos['foto_url'] ?? null,
            'activo' => $datos['activo'] ?? true
        ]);

        return response()->json([
            'success' => true,
            'mensaje' => 'Usuario creado exitosamente.',
            'usuario' => $usuario->makeHidden(['pin_hash', 'password_hash'])
        ], 201);
    }

    public function actualizar(UpdateUsuarioRequest $request, $id)
    {
        $usuario = Usuario::find($id);
        if (!$usuario) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        $datos = $request->validated();
        $esMismoUsuario = $request->user()->id_usuario === $usuario->id_usuario;

        // Reglas de negocio: No puedes desactivarte ni cambiarte el rol a ti mismo
        if ($esMismoUsuario && isset($datos['activo']) && !$datos['activo']) {
            return response()->json(['message' => 'No puedes desactivar tu propia cuenta.'], 422);
        }
        if ($esMismoUsuario && isset($datos['rol']) && $datos['rol'] !== $usuario->rol->value) {
            return response()->json(['message' => 'No puedes cambiar tu propio rol.'], 422);
        }

        // Actualizar campos
        if (isset($datos['nombre'])) $usuario->nombre = $datos['nombre'];
        if (isset($datos['rol'])) $usuario->rol = $datos['rol'];
        if (isset($datos['activo'])) $usuario->activo = $datos['activo'];
        if (isset($datos['foto_url'])) $usuario->foto_url = $datos['foto_url'];

        if (!empty($datos['pin'])) {
            $this->validarPinLibre($datos['pin'], $usuario->id_usuario);
            $usuario->pin_hash = Usuario::generarHash($datos['pin']);
        }
        if (!empty($datos['password'])) {
            $usuario->password_hash = Usuario::generarHash($datos['password']);
        }

        $usuario->save();

        return response()->json([
            'success' => true,
            'mensaje' => 'Usuario actualizado exitosamente.',
            'usuario' => $usuario->makeHidden(['pin_hash', 'password_hash'])
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

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'file' => ['required', 'image', 'max:2048'] // Máximo 2MB
        ]);

        try {
            $path = $request->file('file')->store('avatares', 'public');
            return response()->json([
                'url' => Storage::url($path),
                'filename' => basename($path)
            ]);
        } catch (\Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Error al subir el avatar.'], 500);
        }
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