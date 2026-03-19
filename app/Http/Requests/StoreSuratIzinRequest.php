<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSuratIzinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_izin' => 'required|string|exists:pengajuan_izin,id_izin',
            'isi_surat' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'id_izin.required' => 'ID pengajuan izin wajib diisi.',
            'id_izin.exists' => 'Pengajuan izin tidak ditemukan.',
            'isi_surat.required' => 'Isi surat wajib diisi.',
        ];
    }
}
