<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JadwalShiftCs extends Model
{
    use HasFactory;

    protected $table = 'jadwal_shift_cs';

    protected $fillable = [
        'area_id', // nullable sekarang
        'pjlp_id',
        'tanggal',
        'shift_id',
        'status',
        'keterangan',
        'is_published',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'is_published' => 'boolean',
    ];

    // Status constants
    const STATUS_NORMAL = 'normal';
    const STATUS_LIBUR = 'libur';
    const STATUS_LIBUR_HARI_RAYA = 'libur_hari_raya';
    const STATUS_CUTI = 'cuti';
    const STATUS_IZIN = 'izin';
    const STATUS_SAKIT = 'sakit';
    const STATUS_ALPHA = 'alpha';

    const STATUS_LABELS = [
        self::STATUS_NORMAL => 'Kerja',
        self::STATUS_LIBUR => 'Libur (L)',
        self::STATUS_LIBUR_HARI_RAYA => 'Hari Raya (R)',
        self::STATUS_CUTI => 'Cuti',
        self::STATUS_IZIN => 'Izin',
        self::STATUS_SAKIT => 'Sakit',
        self::STATUS_ALPHA => 'Alpha',
    ];

    const STATUS_COLORS = [
        self::STATUS_NORMAL => 'primary',
        self::STATUS_LIBUR => 'warning',
        self::STATUS_LIBUR_HARI_RAYA => 'danger',
        self::STATUS_CUTI => 'info',
        self::STATUS_IZIN => 'secondary',
        self::STATUS_SAKIT => 'dark',
        self::STATUS_ALPHA => 'danger',
    ];

    // Relationships
    public function area(): BelongsTo
    {
        return $this->belongsTo(MasterAreaCs::class, 'area_id');
    }

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class, 'pjlp_id');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Accessors
    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? '-';
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function getDisplayTextAttribute(): string
    {
        if ($this->status === self::STATUS_LIBUR) {
            return 'LIBUR';
        }
        if ($this->status === self::STATUS_LIBUR_HARI_RAYA) {
            return 'LIBUR';
        }
        if ($this->status !== self::STATUS_NORMAL) {
            return strtoupper(substr($this->status, 0, 1));
        }
        return $this->shift?->nama ?? '-';
    }

    public function getDisplayColorAttribute(): string
    {
        if ($this->status === self::STATUS_LIBUR) {
            return 'warning'; // kuning
        }
        if ($this->status === self::STATUS_LIBUR_HARI_RAYA) {
            return 'danger'; // merah
        }
        if ($this->status !== self::STATUS_NORMAL) {
            return self::STATUS_COLORS[$this->status] ?? 'secondary';
        }

        // Warna berdasarkan shift (menggunakan custom class)
        $shiftNama = strtolower($this->shift?->nama ?? '');
        return match ($shiftNama) {
            'pagi' => 'shift-pagi',
            'siang' => 'shift-siang',
            'malam' => 'shift-malam',
            default => 'secondary',
        };
    }

    public function getDisplayColorHexAttribute(): string
    {
        if ($this->status === self::STATUS_LIBUR) {
            return '#f59f00'; // kuning/warning
        }
        if ($this->status === self::STATUS_LIBUR_HARI_RAYA) {
            return '#d63939'; // merah/danger
        }
        if ($this->status === self::STATUS_CUTI) {
            return '#4299e1'; // info
        }
        if ($this->status === self::STATUS_IZIN) {
            return '#667382'; // secondary
        }
        if ($this->status === self::STATUS_SAKIT) {
            return '#1d273b'; // dark
        }
        if ($this->status === self::STATUS_ALPHA) {
            return '#d63939'; // danger
        }

        // Warna berdasarkan shift untuk status normal (seragam dengan jadwal kerja CS)
        $shiftNama = strtolower($this->shift?->nama ?? '');
        return match ($shiftNama) {
            'pagi' => '#cce5ff',      // biru muda
            'siang' => '#fff3cd',     // kuning/cream
            'malam' => '#f8c8dc',     // pink muda
            default => '#667382',     // secondary
        };
    }

    // Scopes
    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    public function scopeByPjlp($query, $pjlpId)
    {
        return $query->where('pjlp_id', $pjlpId);
    }

    public function scopeByTanggal($query, $tanggal)
    {
        return $query->whereDate('tanggal', $tanggal);
    }

    public function scopeByBulan($query, int $bulan, int $tahun)
    {
        return $query->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun);
    }

    public function scopeKerja($query)
    {
        return $query->where('status', self::STATUS_NORMAL);
    }

    // Helper untuk menentukan apakah PJLP kerja di tanggal ini
    public function isKerja(): bool
    {
        return $this->status === self::STATUS_NORMAL && $this->shift_id !== null;
    }

    public function isLibur(): bool
    {
        return in_array($this->status, [self::STATUS_LIBUR, self::STATUS_LIBUR_HARI_RAYA]);
    }
}
