<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        // Como el frontend envía todo el objeto al editar, usamos las mismas reglas
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'string'],
            'tipo' => ['required', 'string'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'stock_ml' => ['nullable', 'integer', 'min:0'],
            'stock_minimo_ml' => ['nullable', 'integer', 'min:0'],
            'unidad_medida' => ['required', 'string'],
            'imagen_url' => ['nullable', 'string'],
        ];
    }
}