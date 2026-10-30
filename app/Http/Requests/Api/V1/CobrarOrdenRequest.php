<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\MetodoPago;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CobrarOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'metodo_pago' => ['required', Rule::enum(MetodoPago::class)],
            'monto_pagado' => ['required', 'numeric', 'min:0'],
            'propina' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'metodo_pago.required' => 'El método de pago es obligatorio.',
            'metodo_pago.Illuminate\Validation\Rules\Enum' => 'El método de pago seleccionado no es válido.',
            'monto_pagado.required' => 'El monto pagado es obligatorio.',
        ];
    }
}