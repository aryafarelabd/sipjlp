<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CopyJadwalShiftCsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('jadwal-cs.manage');
    }

    public function rules(): array
    {
        return [
            'area_id'        => 'nullable|exists:master_area_cs,id',
            'source_date'    => 'required|date',
            'target_dates'   => 'required|array',
            'target_dates.*' => 'date',
        ];
    }
}
