<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHariLiburRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal'    => ['required', 'date'],
            'keterangan' => ['required', 'string', 'max:255'],
            'id_kantor'  => ['nullable', 'exists:kantor,id_kantor'],
        ];
    }
}
