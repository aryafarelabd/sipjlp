<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LembarKerjaCs extends Model
{
    protected $table = 'lembar_kerja_cs';

    protected $fillable = [
        'pjlp_id',
        'area_id',
        'shift_id',
        'tanggal',
        'kegiatan_periodik',
        'kegiatan_extra_job',
        'foto_dokumentasi',
        'deskripsi_foto',
        'catatan',
        'status',
        'submitted_at',
        'validated_by',
        'validated_at',
        'catatan_koordinator',
    ];

    protected $casts = [
        'tanggal'            => 'date',
        'kegiatan_periodik'  => 'array',
        'kegiatan_extra_job' => 'array',
        'foto_dokumentasi'   => 'array',
        'submitted_at'       => 'datetime',
        'validated_at'       => 'datetime',
    ];

    const STATUS_DRAFT      = 'draft';
    const STATUS_SUBMITTED  = 'submitted';
    const STATUS_VALIDATED  = 'validated';
    const STATUS_REJECTED   = 'rejected';

    const STATUS_LABELS = [
        self::STATUS_DRAFT     => 'Draft',
        self::STATUS_SUBMITTED => 'Menunggu Validasi',
        self::STATUS_VALIDATED => 'Tervalidasi',
        self::STATUS_REJECTED  => 'Ditolak',
    ];

    const STATUS_COLORS = [
        self::STATUS_DRAFT     => 'secondary',
        self::STATUS_SUBMITTED => 'warning',
        self::STATUS_VALIDATED => 'success',
        self::STATUS_REJECTED  => 'danger',
    ];

    // ── Relationships ────────────────────────────────────────────

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

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? '-';
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function isDraft(): bool      { return $this->status === self::STATUS_DRAFT; }
    public function isSubmitted(): bool  { return $this->status === self::STATUS_SUBMITTED; }
    public function isValidated(): bool  { return $this->status === self::STATUS_VALIDATED; }
    public function isRejected(): bool   { return $this->status === self::STATUS_REJECTED; }
    public function canEdit(): bool      { return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]); }
    public function canSubmit(): bool    { return $this->status === self::STATUS_DRAFT; }
    public function canValidate(): bool  { return $this->status === self::STATUS_SUBMITTED; }

    public function submit(): void
    {
        $this->update(['status' => self::STATUS_SUBMITTED, 'submitted_at' => now()]);
    }

    public function validateLk(int $userId, ?string $catatan = null): void
    {
        $this->update([
            'status'               => self::STATUS_VALIDATED,
            'validated_by'         => $userId,
            'validated_at'         => now(),
            'catatan_koordinator'  => $catatan,
        ]);
    }

    public function rejectLk(int $userId, ?string $catatan = null): void
    {
        $this->update([
            'status'               => self::STATUS_REJECTED,
            'validated_by'         => $userId,
            'validated_at'         => now(),
            'catatan_koordinator'  => $catatan,
        ]);
    }

    public function getTotalKegiatanAttribute(): int
    {
        return count($this->kegiatan_periodik ?? []) + count($this->kegiatan_extra_job ?? []);
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeByPjlp($query, int $pjlpId)
    {
        return $query->where('pjlp_id', $pjlpId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByBulan($query, int $bulan, int $tahun)
    {
        return $query->whereYear('tanggal', $tahun)->whereMonth('tanggal', $bulan);
    }
}
