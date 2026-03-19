<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDivisiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_divisi' => ['required', 'string', 'max:255', 'unique:divisi,nama_divisi'],
        ];
    }
}
