<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTukarShiftRequest extends FormRequest
{

    public function authorize(): bool
    {

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_user_1' => 'required|exists:users,id',
            'id_jadwal_1' => 'required|exists:jadwal_kerja,id_jadwal',
            'id_user_2' => 'required|exists:users,id|different:id_user_1',
            'id_jadwal_2' => 'required|exists:jadwal_kerja,id_jadwal|different:id_jadwal_1',
            'keterangan' => 'nullable|string|max:1000'
        ];
    }

    public function messages(): array
    {
        return [
            'id_user_1.required' => 'Pegawai pertama harus dipilih.',
            'id_user_1.exists' => 'Pegawai pertama tidak valid.',
            'id_jadwal_1.required' => 'Jadwal pegawai pertama harus dipilih.',
            'id_jadwal_1.exists' => 'Jadwal pegawai pertama tidak valid.',
            'id_user_2.required' => 'Pegawai kedua harus dipilih.',
            'id_user_2.exists' => 'Pegawai kedua tidak valid.',
            'id_user_2.different' => 'Pegawai kedua tidak boleh sama dengan pegawai pertama.',
            'id_jadwal_2.required' => 'Jadwal pegawai kedua harus dipilih.',
            'id_jadwal_2.exists' => 'Jadwal pegawai kedua tidak valid.',
            'id_jadwal_2.different' => 'Jadwal pegawai kedua tidak boleh sama dengan jadwal pegawai pertama.',
        ];
    }
}
