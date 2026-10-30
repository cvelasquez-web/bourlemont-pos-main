<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AgregarIngredienteRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id_producto_final' => ['required', 'integer', 'exists:productos,id_producto'],
            'id_insumo_base' => ['required', 'integer', 'exists:productos,id_producto'],
            'cantidad_descontar_ml' => ['required', 'integer', 'min:1'],
        ];
    }
}