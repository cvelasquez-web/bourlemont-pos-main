<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class LoginAdminRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string'],
            'password' => ['required', 'string']
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre de usuario es obligatorio.',
            'password.required' => 'La contraseña es obligatoria.'
        ];
    }
}
