<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_role' => ['required', 'string', 'max:50', 'unique:roles,nama_role'],
            'id_permissions' => ['nullable', 'array'],
            'id_permissions.*' => ['exists:permissions,id_permission'],
        ];
    }
    
    public function messages(): array
    {
        return [
            'nama_role.required' => 'Nama role wajib diisi.',
            'nama_role.unique'   => 'Nama role ini sudah ada.',
        ];
    }
}