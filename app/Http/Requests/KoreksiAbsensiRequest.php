<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KoreksiAbsensiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canAny(['absensi.view-unit', 'absensi.view-all']);
    }

    public function rules(): array
    {
        return [
            'tanggal'    => 'required|date',
            'pjlp_id'    => 'required|exists:pjlp,id',
            'status'     => 'required|in:hadir,terlambat,alpha,izin,cuti,sakit,libur',
            'jam_masuk'  => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'keterangan' => 'nullable|string|max:500',
        ];
    }
}
