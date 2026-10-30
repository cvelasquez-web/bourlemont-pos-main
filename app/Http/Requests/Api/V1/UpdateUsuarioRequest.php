<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'rol' => ['required', 'in:ADMIN,BARTENDER'],
            'pin' => ['nullable', 'digits:4'], // Opcional (solo si se quiere resetear)
            'password' => ['nullable', 'string', 'min:4'], // Opcional
            'foto_url' => ['nullable', 'string'],
            'activo' => ['required', 'boolean']
        ];
    }
}