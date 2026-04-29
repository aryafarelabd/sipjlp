<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LembarKerjaDetail extends Model
{
    use HasFactory;

    protected $table = 'lembar_kerja_detail';

    protected $fillable = [
        'lembar_kerja_id',
        'jam',
        'pekerjaan',
        'lokasi_id',
        'keterangan',
        'foto',
    ];

    public function lembarKerja(): BelongsTo
    {
        return $this->belongsTo(LembarKerja::class, 'lembar_kerja_id');
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(Lokasi::class, 'lokasi_id');
    }
}
