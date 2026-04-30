<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLaporanParkirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis'             => 'required|in:roda_4,roda_2',
            'shift_id'          => 'required|exists:shifts,id',
            'jumlah_kendaraan'  => 'required|integer|min:0',
            'catatan'           => 'nullable|string|max:500',
            'fotos'             => 'required|array|min:1|max:10',
            'fotos.*'           => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'fotos.required'    => 'Minimal 1 foto wajib diupload.',
            'shift_id.required' => 'Pilih shift laporan.',
            'shift_id.exists'   => 'Shift yang dipilih tidak valid.',
            'fotos.min'         => 'Minimal 1 foto wajib diupload.',
            'fotos.max'         => 'Maksimal 10 foto per laporan.',
            'fotos.*.image'     => 'File harus berupa gambar.',
            'fotos.*.max'       => 'Ukuran setiap foto maksimal 5MB.',
        ];
    }
}
