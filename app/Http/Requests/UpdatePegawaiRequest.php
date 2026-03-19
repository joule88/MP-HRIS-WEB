<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePegawaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('pegawai');

        return [
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'unique:users,email,' . $this->route('pegawai')
            ],
            'no_telp' => ['nullable', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'id_divisi' => ['required', 'exists:divisi,id_divisi'],
            'id_jabatan' => ['required', 'exists:jabatan,id_jabatan'],
            'id_kantor' => ['required', 'exists:kantor,id_kantor'],
            'status_aktif' => ['required', 'in:0,1'],
            'tgl_bergabung' => ['required', 'date'],
            'foto' => ['nullable', 'image', 'max:5120'],
            'sisa_cuti' => ['nullable', 'integer', 'min:0'],
            'id_role' => ['required', 'exists:roles,id_role'],
        ];
    }
}