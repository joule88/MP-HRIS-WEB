<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePresensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'required|file|mimes:jpg,jpeg,png|max:5120',
            'status' => 'required|in:masuk,pulang',
            'keterangan_luar_radius' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'foto.required' => 'Foto wajah wajib diambil.',
            'foto.file' => 'Format foto tidak valid.',
            'foto.mimes' => 'Foto harus berformat JPG atau PNG.',
            'foto.max' => 'Ukuran foto maksimal 5MB.',
            'status.required' => 'Status presensi wajib diisi.',
            'status.in' => 'Status presensi harus masuk atau pulang.',
        ];
    }
}
