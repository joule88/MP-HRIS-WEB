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
            'nik'          => ['nullable', 'string', 'max:50', Rule::unique('users', 'nik')->ignore($id)],
            'email'        => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($id),
            ],
            'no_telp'      => ['nullable', 'string', 'max:15', 'regex:/^(\+62|62|0)[0-9]{8,11}$/'],
            'alamat'       => ['nullable', 'string'],
            'id_divisi'    => ['required', 'exists:divisi,id_divisi'],
            'id_jabatan'   => ['required', 'exists:jabatan,id_jabatan'],
            'id_kantor'    => ['required', 'exists:kantor,id_kantor'],
            'status_aktif' => ['required', 'in:0,1'],
            'tgl_bergabung' => ['required', 'date'],
            'foto'         => ['nullable', 'image', 'max:5120'],
            'sisa_cuti'    => ['nullable', 'integer', 'min:0'],
            'id_role'      => ['required', 'exists:roles,id_role'],
        ];
    }

    public function messages(): array
    {
        return [
            'nik.unique'        => 'NIK sudah terdaftar di sistem.',
            'email.unique'      => 'Email ini sudah digunakan oleh pegawai lain.',
            'id_divisi.exists'  => 'Divisi yang dipilih tidak valid.',
            'id_jabatan.exists' => 'Jabatan yang dipilih tidak valid.',
            'id_kantor.exists'  => 'Kantor yang dipilih tidak valid.',
            'id_role.exists'    => 'Role yang dipilih tidak valid.',
            'status_aktif.in'   => 'Status pegawai tidak valid.',
            'foto.max'          => 'Ukuran foto tidak boleh lebih dari 5MB.',
            'sisa_cuti.min'     => 'Sisa cuti tidak boleh negatif.',
            'no_telp.regex'     => 'Format nomor telepon tidak valid. Gunakan format: 08xx, +62xx, atau 62xx.',
        ];
    }
}