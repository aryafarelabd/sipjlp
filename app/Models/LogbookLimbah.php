<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogbookLimbah extends Model
{
    protected $table = 'logbook_limbah';

    protected $fillable = [
        'pjlp_id',
        'area_id',
        'shift_id',
        'tanggal',
        'berat_domestik',
        'berat_kompos',
        'catatan',
    ];

    protected $casts = [
        'tanggal'        => 'date',
        'berat_domestik' => 'decimal:2',
        'berat_kompos'   => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────────

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(MasterAreaCs::class, 'area_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(LogbookLimbahFoto::class, 'logbook_id');
    }

    public function fotosApd(): HasMany
    {
        return $this->hasMany(LogbookLimbahFoto::class, 'logbook_id')->where('kategori', 'apd');
    }

    public function fotosTimbangan(): HasMany
    {
        return $this->hasMany(LogbookLimbahFoto::class, 'logbook_id')->where('kategori', 'timbangan');
    }

    // ── Scopes ──────────────────────────────────────────────────────

    public function scopeByBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }

    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }
}
