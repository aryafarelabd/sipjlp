<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogbookHepafilter extends Model
{
    protected $table = 'logbook_hepafilter';

    protected $fillable = [
        'pjlp_id',
        'tanggal',
        'ruang_poli_gigi',
        'ruang_poli_paru',
        'ruang_igd_isolasi',
        'ruang_perina',
        'ruang_cssd',
        'ruang_bayanaka',
        'ruang_kaivan',
        'ruang_elektromedis',
        'rumah_dinas',
        'catatan',
    ];

    protected $casts = [
        'tanggal'           => 'date',
        'ruang_poli_gigi'   => 'boolean',
        'ruang_poli_paru'   => 'boolean',
        'ruang_igd_isolasi' => 'boolean',
        'ruang_perina'      => 'boolean',
        'ruang_cssd'        => 'boolean',
        'ruang_bayanaka'    => 'boolean',
        'ruang_kaivan'      => 'boolean',
        'ruang_elektromedis'=> 'boolean',
        'rumah_dinas'       => 'boolean',
    ];

    const RUANGAN = [
        'ruang_poli_gigi'    => 'Ruang Poli Gigi',
        'ruang_poli_paru'    => 'Ruang Poli Paru',
        'ruang_igd_isolasi'  => 'Ruang IGD Isolasi',
        'ruang_perina'       => 'Ruang Perina',
        'ruang_cssd'         => 'Ruang CSSD',
        'ruang_bayanaka'     => 'Ruang Bayanaka',
        'ruang_kaivan'       => 'Ruang Kaivan',
        'ruang_elektromedis' => 'Ruang Elektromedis',
        'rumah_dinas'        => 'Rumah Dinas / Rumah Bawah',
    ];

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(LogbookHepafilterFoto::class, 'logbook_hepafilter_id');
    }

    public function getTotalRuanganAttribute(): int
    {
        $count = 0;
        foreach (array_keys(self::RUANGAN) as $field) {
            if ($this->$field) $count++;
        }
        return $count;
    }

    public function getRuanganDibersihkanAttribute(): array
    {
        $result = [];
        foreach (self::RUANGAN as $field => $label) {
            if ($this->$field) $result[] = $label;
        }
        return $result;
    }

    // Scopes
    public function scopeByBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }
}
