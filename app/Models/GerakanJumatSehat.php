<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GerakanJumatSehat extends Model
{
    protected $table = 'gerakan_jumat_sehat';

    protected $fillable = ['pjlp_id', 'unit', 'tanggal', 'waktu', 'foto'];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class);
    }

    public function getFotoUrlAttribute(): string
    {
        return asset('storage/' . $this->foto);
    }

    public function scopeByBulan($query, int $bulan, int $tahun)
    {
        return $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }

    public function scopeByUnit($query, string $unit)
    {
        return $query->where('unit', $unit);
    }
}
