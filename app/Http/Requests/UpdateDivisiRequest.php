<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDivisiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        $id = $this->route('divisi');

        return [

            'nama_divisi' => ['required', 'string', 'max:255', 'unique:divisi,nama_divisi,'.$id.',id_divisi'],
        ];
    }
}
