<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogbookB3 extends Model
{
    protected $table = 'logbook_b3';

    protected $fillable = [
        'pjlp_id', 'area_id', 'shift_id', 'tanggal',
        // Plastik Kuning
        'pk_lt1', 'pk_igd', 'pk_lt2', 'pk_ok',
        'pk_lt3', 'pk_lt4', 'pk_utilitas', 'pk_taman',
        // Safety Box
        'safety_box_asal', 'safety_box_kg',
        // Limbah Cair
        'cair_asal', 'cair_kg',
        // Hepafilter
        'hepafilter_asal', 'hepafilter_kg',
        // Non Infeksius
        'non_infeksius_jenis', 'non_infeksius_kg',
        'catatan',
    ];

    protected $casts = [
        'tanggal'          => 'date',
        'pk_lt1'           => 'decimal:2',
        'pk_igd'           => 'decimal:2',
        'pk_lt2'           => 'decimal:2',
        'pk_ok'            => 'decimal:2',
        'pk_lt3'           => 'decimal:2',
        'pk_lt4'           => 'decimal:2',
        'pk_utilitas'      => 'decimal:2',
        'pk_taman'         => 'decimal:2',
        'safety_box_kg'    => 'decimal:2',
        'cair_kg'          => 'decimal:2',
        'hepafilter_kg'    => 'decimal:2',
        'non_infeksius_kg' => 'decimal:2',
    ];

    // Label lokasi plastik kuning
    public const PK_LOKASI = [
        'pk_lt1'       => 'Lantai 1',
        'pk_igd'       => 'IGD',
        'pk_lt2'       => 'Lantai 2',
        'pk_ok'        => 'OK',
        'pk_lt3'       => 'Lantai 3',
        'pk_lt4'       => 'Lantai 4',
        'pk_utilitas'  => 'Utilitas',
        'pk_taman'     => 'Taman / Halaman',
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
        return $this->hasMany(LogbookB3Foto::class, 'logbook_b3_id');
    }

    public function fotosApd(): HasMany
    {
        return $this->hasMany(LogbookB3Foto::class, 'logbook_b3_id')->where('kategori', 'apd');
    }

    public function fotosTimbangan(): HasMany
    {
        return $this->hasMany(LogbookB3Foto::class, 'logbook_b3_id')->where('kategori', 'timbangan');
    }

    // ── Accessors ─────────────────────────────────────────────────

    /** Total berat plastik kuning semua lokasi */
    public function getTotalPkAttribute(): float
    {
        return collect(array_keys(self::PK_LOKASI))
            ->sum(fn($col) => (float) ($this->$col ?? 0));
    }

    /** Total semua limbah B3 dalam 1 record */
    public function getTotalBeratAttribute(): float
    {
        return $this->total_pk
            + (float) ($this->safety_box_kg    ?? 0)
            + (float) ($this->cair_kg          ?? 0)
            + (float) ($this->hepafilter_kg    ?? 0)
            + (float) ($this->non_infeksius_kg ?? 0);
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeByBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }

    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }
}
