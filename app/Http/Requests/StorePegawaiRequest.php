<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'id_divisi' => ['required', 'exists:divisi,id_divisi'],
            'id_jabatan' => ['required', 'exists:jabatan,id_jabatan'],
            'id_kantor' => ['required', 'exists:kantor,id_kantor'],
            'tgl_bergabung' => ['required', 'date'],
            'foto' => ['nullable', 'image', 'max:5120'],
            'sisa_cuti' => ['nullable', 'integer', 'min:0'],
            'id_role' => ['required', 'exists:roles,id_role'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.unique' => 'NIK sudah terdaftar di sistem.',
            'email.unique' => 'Email ini sudah digunakan oleh pegawai lain.',
            'id_divisi.exists' => 'Divisi yang dipilih tidak valid.',
            'id_role.exists' => 'Role yang dipilih tidak valid.',
        ];
    }
}