<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreUsuarioRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'rol' => ['required', 'in:ADMIN,BARTENDER'],
            'pin' => ['nullable', 'digits:4'], // El PIN es obligatorio solo para bartenders
            'password' => ['nullable', 'string', 'min:4'], // La contraseña es obligatoria solo para admin
            'foto_url' => ['nullable', 'string'],
            'activo' => ['boolean']
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->rol === 'BARTENDER' && !$this->pin) {
                $validator->errors()->add('pin', 'El PIN es obligatorio para los bartenders.');
            }
            if ($this->rol === 'ADMIN' && !$this->password) {
                $validator->errors()->add('password', 'La contraseña es obligatoria para los administradores.');
            }
        });
    }
}