<?php

namespace App\Models;

use App\Enums\StatusLembarKerja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LembarKerja extends Model
{
    use HasFactory;

    protected $table = 'lembar_kerja';

    protected $fillable = [
        'pjlp_id',
        'tanggal',
        'status',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'status'  => StatusLembarKerja::class,
    ];

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class, 'pjlp_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(LembarKerjaDetail::class, 'lembar_kerja_id');
    }

    public function validasi(): HasOne
    {
        return $this->hasOne(LembarKerjaValidasi::class, 'lembar_kerja_id');
    }

    public function scopeForPjlp($query, int $pjlpId)
    {
        return $query->where('pjlp_id', $pjlpId);
    }

    public function scopeForDate($query, $tanggal)
    {
        return $query->whereDate('tanggal', $tanggal);
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function canBeEdited(): bool
    {
        return $this->status === StatusLembarKerja::DRAFT;
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === StatusLembarKerja::DRAFT && $this->details()->exists();
    }

    public function submit(): void
    {
        $this->update(['status' => StatusLembarKerja::SUBMITTED]);
    }

    public function validate(User $validator, ?string $catatan = null): void
    {
        $this->update(['status' => StatusLembarKerja::DIVALIDASI]);

        $this->validasi()->updateOrCreate(
            ['lembar_kerja_id' => $this->id],
            [
                'validated_by' => $validator->id,
                'validated_at' => now(),
                'catatan'      => $catatan,
            ]
        );
    }

    public function reject(User $validator, string $catatan): void
    {
        $this->update(['status' => StatusLembarKerja::DITOLAK]);

        $this->validasi()->updateOrCreate(
            ['lembar_kerja_id' => $this->id],
            [
                'validated_by' => $validator->id,
                'validated_at' => now(),
                'catatan'      => $catatan,
            ]
        );
    }
}
