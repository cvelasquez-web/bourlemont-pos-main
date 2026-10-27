<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrdenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'productos' => [
                'required',
                'array',
                'min:1',
            ],

            'productos.*.id_producto' => [
                'required',
                'integer',
                'min:1',
            ],

            'productos.*.cantidad' => [
                'required',
                'integer',
                'min:1',
            ],

            'productos.*.descuento_porcentaje' => [
                'nullable',
                'numeric',
                'between:0,100',
            ],
        ];
    }
}
