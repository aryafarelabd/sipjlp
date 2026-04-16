<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LogbookDekontaminasi extends Model
{
    protected $table = 'logbook_dekontaminasi';

    protected $fillable = [
        'pjlp_id',
        'shift_id',
        'tanggal',
        'lokasi',
        'catatan',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(LogbookDekontaminasiFoto::class, 'logbook_dekontaminasi_id');
    }

    public function scopeByBulan($query, $bulan, $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }
}
