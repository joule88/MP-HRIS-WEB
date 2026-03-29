<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKantorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_kantor' => ['required', 'string', 'max:255'],
            'tipe'        => ['required', 'string', 'in:Pusat,Cabang'],
            'alamat'      => ['nullable', 'string'],

            'latitude'    => ['required', 'numeric', 'between:-90,90'],
            'longitude'   => ['required', 'numeric', 'between:-180,180'],
            'radius'      => ['required', 'integer', 'min:10'],
        ];
    }
}
