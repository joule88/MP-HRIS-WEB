<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_mulai'  => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'id_shift'       => ['required', 'exists:shift_kerja,id_shift'],
            'user_ids'       => ['required', 'array', 'min:1'],
            'user_ids.*'     => ['exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_mulai.required'      => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required'    => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'id_shift.required'           => 'Shift kerja wajib dipilih.',
            'id_shift.exists'             => 'Shift yang dipilih tidak valid.',
            'user_ids.required'           => 'Pilih minimal 1 pegawai.',
            'user_ids.min'                => 'Pilih minimal 1 pegawai.',
            'user_ids.*.exists'           => 'Salah satu pegawai yang dipilih tidak valid.',
        ];
    }
}
