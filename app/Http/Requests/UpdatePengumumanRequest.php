<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengumumanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul' => 'required|string|max:80',
            'isi' => 'required|string|max:500',
            'tanggal' => 'required|date',
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required' => 'Judul pengumuman wajib diisi.',
            'judul.max' => 'Judul maksimal 80 karakter.',
            'isi.required' => 'Isi pengumuman wajib diisi.',
            'tanggal.required' => 'Tanggal tayang wajib diisi.',
        ];
    }
}