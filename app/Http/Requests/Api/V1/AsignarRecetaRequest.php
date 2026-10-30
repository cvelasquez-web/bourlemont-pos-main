<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class AsignarRecetaRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'id_producto_final' => ['required', 'integer', 'exists:productos,id_producto'],
            'ingredientes' => ['required', 'array', 'min:1'],
            'ingredientes.*.id_insumo_base' => ['required', 'integer', 'exists:productos,id_producto'],
            'ingredientes.*.cantidad_descontar_ml' => ['required', 'integer', 'min:1'],
        ];
    }
}