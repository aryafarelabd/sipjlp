<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AbsenMasukRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->pjlp !== null;
    }

    public function rules(): array
    {
        return [
            'foto'      => 'required|image|mimes:jpeg,jpg,png|max:5120',
            'latitude'  => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }

    public function messages(): array
    {
        return [
            'foto.required' => 'Foto selfie wajib diambil sebelum absen.',
            'foto.image'    => 'File harus berupa gambar.',
            'foto.max'      => 'Ukuran foto maksimal 5MB.',
        ];
    }
}
