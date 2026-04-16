<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LembarKerjaSecurity extends Model
{
    use SoftDeletes;

    protected $table = 'lembar_kerja_security';

    protected $fillable = [
        'tanggal',
        'area_id',
        'pjlp_id',
        'shift_id',
        'status',
        'catatan_pjlp',
        'catatan_validator',
        'validated_by',
        'validated_at',
    ];

    // Status Constants
    const STATUS_DRAFT = 'draft';
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_VALIDATED = 'validated';
    const STATUS_REJECTED = 'rejected';

    // --- Relasi ---

    public function details(): HasMany
    {
        return $this->hasMany(LembarKerjaSecurityDetail::class, 'lembar_kerja_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(MasterAreaCs::class, 'area_id');
    }

    public function pjlp(): BelongsTo
    {
        return $this->belongsTo(Pjlp::class, 'pjlp_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'shift_id');
    }

    // --- Helpers ---

    public function getCompletionPercentageAttribute(): int
    {
        $total = $this->details()->count();
        if ($total == 0) return 0;

        $completed = $this->details()->where('is_completed', true)->count();
        return (int) round(($completed / $total) * 100);
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED]);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function submit()
    {
        return $this->update(['status' => self::STATUS_SUBMITTED]);
    }

    public function validate($userId, $notes = null)
    {
        return $this->update([
            'status' => self::STATUS_VALIDATED,
            'validated_by' => $userId,
            'validated_at' => now(),
            'catatan_validator' => $notes
        ]);
    }

    public function reject($userId, $notes = null)
    {
        return $this->update([
            'status' => self::STATUS_REJECTED,
            'validated_by' => $userId,
            'validated_at' => now(),
            'catatan_validator' => $notes
        ]);
    }
}
