<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKegiatanLkCs extends Model
{
    protected $table = 'master_kegiatan_lk_cs';

    protected $fillable = ['nama', 'tipe', 'urutan', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'urutan'    => 'integer',
    ];

    const TIPE_PERIODIK   = 'periodik';
    const TIPE_EXTRA_JOB  = 'extra_job';

    const TIPE_LABELS = [
        self::TIPE_PERIODIK  => 'Kegiatan Periodik',
        self::TIPE_EXTRA_JOB => 'Kegiatan Extra Job',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePeriodik($query)
    {
        return $query->where('tipe', self::TIPE_PERIODIK);
    }

    public function scopeExtraJob($query)
    {
        return $query->where('tipe', self::TIPE_EXTRA_JOB);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan')->orderBy('nama');
    }

    public function getTipeLabelAttribute(): string
    {
        return self::TIPE_LABELS[$this->tipe] ?? $this->tipe;
    }
}
