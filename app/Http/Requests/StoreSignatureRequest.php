<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSignatureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'signature_data' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'signature_data.required' => 'Data tanda tangan wajib diisi.',
        ];
    }
}
