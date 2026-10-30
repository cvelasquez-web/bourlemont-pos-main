<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\RolUsuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CrearUsuarioRequest extends FormRequest
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
            'nombre' => ['required', 'string', 'max:100'],
            'rol' => ['required', Rule::enum(RolUsuario::class)],
            'pin' => ['required', 'digits:4'],
            'password' => ['nullable', 'string', 'min:8'],
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
            'nombre.required' => 'El nombre del colaborador es obligatorio.',
            'nombre.max' => 'El nombre no puede exceder los 100 caracteres.',
            'rol.required' => 'El rol asignado es obligatorio.',
            'rol.Illuminate\Validation\Rules\Enum' => 'El rol seleccionado no es válido.',
            'pin.required' => 'El PIN de seguridad de 4 dígitos es obligatorio.',
            'pin.digits' => 'El PIN debe constar exactamente de 4 dígitos numéricos.',
            'password.min' => 'La contraseña administrativa debe tener al menos 8 caracteres.',
        ];
    }
}
