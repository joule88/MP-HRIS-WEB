<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {

        return [
            'nama_role' => ['required', 'string', 'max:50', 'unique:roles,nama_role,' . $this->route('role') . ',id_role'],
            'id_permissions' => ['nullable', 'array'],
            'id_permissions.*' => ['exists:permissions,id_permission'],
        ];
    }
}
