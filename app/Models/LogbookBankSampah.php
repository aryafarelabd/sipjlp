<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogbookBankSampah extends Model
{
    protected $table = 'logbook_bank_sampah';

    protected $fillable = [
        'pjlp_id',
        'tanggal',
        'kardus',
        'jerigen_besar',
        'jerigen_kecil',
        'botol',
        'plastik',
        'baja',
        'paralon',
        'diplek',
        'kertas',
        'besi',
        'seng',
        'catatan',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'kardus'       => 'float',
        'jerigen_besar'=> 'float',
        'jerigen_kecil'=> 'float',
        'botol'        => 'float',
        'plastik'      => 'float',
        'baja'         => 'float',
        'paralon'      => 'float',
        'diplek'       => 'float',
        'kertas'       => 'float',
        'besi'         => 'float',
        'seng'         => 'float',
    ];

    const JENIS = [
        'kardus'        => 'Kardus',
        'jerigen_besar' => 'Jerigen Besar',
        'jerigen_kecil' => 'Jerigen Kecil',
        'botol'         => 'Botol',
        'plastik'       => 'Plastik',
        'baja'          => 'Baja',
        'paralon'       => 'Paralon',
        'diplek'        => 'Diplek',
        'kertas'        => 'Kertas',
        'besi'          => 'Besi',
        'seng'          => 'Seng',
    ];

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(LogbookBankSampahFoto::class, 'logbook_bank_sampah_id');
    }

    public function getTotalBeratAttribute(): float
    {
        $total = 0;
        foreach (array_keys(self::JENIS) as $field) {
            $total += $this->$field ?? 0;
        }
        return $total;
    }

    public function scopeByBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }
}
