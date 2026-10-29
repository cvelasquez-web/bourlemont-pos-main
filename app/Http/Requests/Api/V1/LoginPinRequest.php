<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class LoginPinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pin' => ['required', 'digits:4']
        ];
    }

    public function messages(): array
    {
        return [
            'pin.required' => 'El PIN es obligatorio.',
            'pin.digits' => 'El PIN debe tener exactamente 4 dígitos.'
        ];
    }
}
