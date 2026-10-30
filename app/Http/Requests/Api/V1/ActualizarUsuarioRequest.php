<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\RolUsuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarUsuarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nombre' => ['sometimes', 'string', 'max:100'],
            'rol' => ['sometimes', Rule::enum(RolUsuario::class)],
            'pin' => ['nullable', 'digits:4'],
            'password' => ['nullable', 'string', 'min:8'],
            'activo' => ['sometimes', 'boolean'],
            'foto_url' => ['nullable', 'string', 'max:2048'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nombre.max' => 'El nombre no puede exceder los 100 caracteres.',
            'rol.Illuminate\Validation\Rules\Enum' => 'El rol seleccionado no es válido.',
            'pin.digits' => 'El PIN debe constar exactamente de 4 dígitos numéricos.',
            'password.min' => 'La contraseña administrativa debe tener al menos 8 caracteres.',
            'activo.boolean' => 'El estado activo debe ser un valor booleano.',
        ];
    }
}
