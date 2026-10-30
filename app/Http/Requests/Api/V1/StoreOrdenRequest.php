<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Ya protegido por auth:sanctum en las rutas
    }

    public function rules(): array
    {
        return [
            'numero_mesa' => ['required', 'string', 'max:50'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id_producto' => ['required', 'integer', 'exists:productos,id_producto'],
            'items.*.cantidad' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    public function messages(): array
    {
        return [
            'numero_mesa.required' => 'El número de mesa es obligatorio.',
            'items.required' => 'Debes agregar al menos un producto a la orden.',
            'items.*.id_producto.exists' => 'Uno de los productos seleccionados no existe en la base de datos.',
        ];
    }
}