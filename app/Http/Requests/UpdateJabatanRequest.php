<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJabatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('jabatan');

        return [
            'nama_jabatan' => ['required', 'string', 'max:255', 'unique:jabatan,nama_jabatan,'.$id.',id_jabatan'],
        ];
    }
}