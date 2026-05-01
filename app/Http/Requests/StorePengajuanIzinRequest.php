<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePengajuanIzinRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        if (!$this->has('id_user') && auth()->check()) {
            $this->merge([
                'id_user' => auth()->id()
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'id_user'        => 'required|exists:users,id',
            'id_jenis_izin'  => 'required|exists:jenis_izin,id_jenis_izin',
            'tanggal_mulai'  => 'required|date',
            'tanggal_selesai'=> 'required|date|after_or_equal:tanggal_mulai',
            'alasan'         => 'required|string|max:500',
            'bukti_file'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'id_user'         => 'pegawai',
            'id_jenis_izin'   => 'jenis izin',
            'tanggal_mulai'   => 'tanggal mulai',
            'tanggal_selesai' => 'tanggal selesai',
            'alasan'          => 'alasan',
            'bukti_file'      => 'bukti file',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'bukti_file.max' => 'Ukuran file maksimal 10MB.',
            'bukti_file.mimes' => 'Format file harus PDF, JPG, JPEG, atau PNG.',
        ];
    }
}
