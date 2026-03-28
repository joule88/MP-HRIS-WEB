<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManualPresensiRequest extends FormRequest
{

    public function authorize(): bool
    {

        return $this->user()->roles->contains(function ($role) {
             return strtolower($role->nama_role) === 'hrd';
        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_user' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'id_status' => 'required|exists:status_presensi,id_status',
            'jam_masuk' => [
                Rule::requiredIf(fn () => in_array($this->id_status, [1, 2])),
                'nullable',
                'date_format:H:i'
            ],
            'jam_pulang' => [
                'nullable',
                'date_format:H:i',
                Rule::when(
                    fn () => $this->jam_masuk && $this->jam_pulang,
                    ['after:jam_masuk']
                ),
            ],
            'alasan_telat' => 'nullable|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'id_user.required' => 'Pegawai harus dipilih.',
            'id_status.required' => 'Status kehadiran harus dipilih.',
            'jam_masuk.required' => 'Jam masuk wajib diisi untuk status Hadir/Terlambat.',
        ];
    }
}
