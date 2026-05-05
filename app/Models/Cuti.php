<?php

namespace App\Models;

use App\Enums\StatusCuti;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cuti extends Model
{
    use HasFactory;

    protected $table = 'cuti';

    protected $fillable = [
        'pjlp_id',
        'danru_id',
        'tanggal_permohonan',
        'jenis_cuti_id',
        'alasan',
        'no_telp',
        'tgl_mulai',
        'tgl_selesai',
        'jumlah_hari',
        'status',
        'approved_by_danru',
        'approved_at_danru',
        'approved_by_chief',
        'approved_at_chief',
        'approved_by',
        'approved_at',
        'alasan_penolakan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_permohonan' => 'datetime',
            'tgl_mulai'          => 'date',
            'tgl_selesai'        => 'date',
            'status'             => StatusCuti::class,
            'approved_at_danru'  => 'datetime',
            'approved_at_chief'  => 'datetime',
            'approved_at'        => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function ($cuti) {
            $cuti->tanggal_permohonan = now();
            $cuti->jumlah_hari = self::hitungJumlahHari($cuti->tgl_mulai, $cuti->tgl_selesai);
        });
    }

    public static function hitungJumlahHari($tglMulai, $tglSelesai): int
    {
        $start = Carbon::parse($tglMulai);
        $end = Carbon::parse($tglSelesai);
        return $start->diffInDays($end) + 1;
    }

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class);
    }

    public function jenisCuti(): BelongsTo
    {
        return $this->belongsTo(JenisCuti::class);
    }

    public function danru(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class, 'danru_id');
    }

    public function approvedByDanru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_danru');
    }

    public function approvedByChief(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_chief');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeForPjlp($query, $pjlpId)
    {
        return $query->where('pjlp_id', $pjlpId);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            StatusCuti::MENUNGGU,
            StatusCuti::MENUNGGU_DANRU,
            StatusCuti::MENUNGGU_CHIEF,
            StatusCuti::MENUNGGU_KOORDINATOR,
        ]);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', StatusCuti::DISETUJUI);
    }

    public function canBeEdited(): bool
    {
        return $this->status->isPending();
    }

    public function approveByDanru(User $user): void
    {
        $this->update([
            'status'             => StatusCuti::MENUNGGU_CHIEF,
            'approved_by_danru'  => $user->id,
            'approved_at_danru'  => now(),
        ]);
    }

    public function approveByChief(User $user): void
    {
        $this->update([
            'status'            => StatusCuti::MENUNGGU_KOORDINATOR,
            'approved_by_chief' => $user->id,
            'approved_at_chief' => now(),
        ]);
    }

    public function approveByPjCs(User $user): void
    {
        $this->update([
            'status'            => StatusCuti::MENUNGGU_KOORDINATOR,
            'approved_by_pj_cs' => $user->id,
            'approved_at_pj_cs' => now(),
        ]);
    }

    public function approve(User $user): void
    {
        $this->update([
            'status'      => StatusCuti::DISETUJUI,
            'approved_by' => $user->id,
            'approved_at' => now(),
        ]);
    }

    public function reject(User $user, string $alasan): void
    {
        $this->update([
            'status'            => StatusCuti::DITOLAK,
            'approved_by'       => $user->id,
            'approved_at'       => now(),
            'alasan_penolakan'  => $alasan,
        ]);
    }
}
