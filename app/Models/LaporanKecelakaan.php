<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanKecelakaan extends Model
{
    protected $table = 'laporan_kecelakaan';

    protected $fillable = [
        'user_id', 'nama_pelapor', 'unit_bagian', 'tanggal', 'waktu',
        'tempat', 'saksi',
        'jumlah_laki', 'jumlah_perempuan', 'nama_korban', 'umur_korban',
        'akibat_mati', 'akibat_luka_berat', 'akibat_luka_ringan', 'keterangan_cedera',
        'kondisi_berbahaya', 'tindakan_berbahaya', 'uraian_kejadian', 'sumber_kejadian',
        'tipe', 'foto_bukti', 'file_formulir',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    const TIPE = [
        'accident'  => 'Accident',
        'incident'  => 'Incident',
        'near_miss' => 'Near Miss',
    ];

    const TIPE_COLOR = [
        'accident'  => 'danger',
        'incident'  => 'warning',
        'near_miss' => 'info',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTotalKorbanAttribute(): int
    {
        return $this->jumlah_laki + $this->jumlah_perempuan;
    }

    public function getTipeLabelAttribute(): string
    {
        return self::TIPE[$this->tipe] ?? $this->tipe;
    }

    public function getTipeColorAttribute(): string
    {
        return self::TIPE_COLOR[$this->tipe] ?? 'secondary';
    }

    public function scopeByBulan($query, int $bulan, int $tahun)
    {
        return $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }
}
