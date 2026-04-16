<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LembarKerjaSecurityDetail extends Model
{
    use SoftDeletes;

    protected $table = 'lembar_kerja_security_detail';

    protected $fillable = [
        'lembar_kerja_id',
        'aktivitas_id',
        'is_completed',
        'waktu_selesai',
        'catatan',
        'foto_before',
        'foto_after',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'waktu_selesai' => 'datetime',
    ];

    public function lembarKerja(): BelongsTo
    {
        return $this->belongsTo(LembarKerjaSecurity::class, 'lembar_kerja_id');
    }

    public function aktivitas(): BelongsTo
    {
        return $this->belongsTo(MasterAktivitasCs::class, 'aktivitas_id');
    }
}
