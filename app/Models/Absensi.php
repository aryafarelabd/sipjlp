<?php

namespace App\Models;

use App\Enums\StatusAbsensi;
use App\Enums\SumberDataAbsensi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'pjlp_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'shift_id',
        'status',
        'menit_terlambat',
        'sumber_data',
        'keterangan',
        'foto_masuk',
        'foto_pulang',
        'latitude_masuk',
        'longitude_masuk',
        'latitude_pulang',
        'longitude_pulang',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jam_masuk' => 'datetime:H:i',
            'jam_pulang' => 'datetime:H:i',
            'status' => StatusAbsensi::class,
            'sumber_data' => SumberDataAbsensi::class,
        ];
    }

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function scopeForPjlp($query, $pjlpId)
    {
        return $query->where('pjlp_id', $pjlpId);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('tanggal', $date);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('tanggal', $year)
                     ->whereMonth('tanggal', $month);
    }

    public function getFotoMasukUrlAttribute(): ?string
    {
        return $this->foto_masuk ? asset('storage/' . $this->foto_masuk) : null;
    }

    public function getFotoPulangUrlAttribute(): ?string
    {
        return $this->foto_pulang ? asset('storage/' . $this->foto_pulang) : null;
    }

    public function hitungKeterlambatan(): int
    {
        if (!$this->jam_masuk || !$this->shift) {
            return 0;
        }

        $jamMasuk = \Carbon\Carbon::parse($this->jam_masuk);
        $batasWaktu = \Carbon\Carbon::parse($this->shift->jam_mulai)
            ->addMinutes($this->shift->toleransi_terlambat);

        if ($jamMasuk->gt($batasWaktu)) {
            return $jamMasuk->diffInMinutes($this->shift->jam_mulai);
        }

        return 0;
    }
}
