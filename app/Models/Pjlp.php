<?php

namespace App\Models;

use App\Enums\StatusPjlp;
use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pjlp extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pjlp';

    protected $fillable = [
        'user_id',
        'nip',
        'badge_number',
        'nama',
        'unit',
        'jabatan',
        'no_telp',
        'alamat',
        'tanggal_bergabung',
        'status',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'unit' => UnitType::class,
            'status' => StatusPjlp::class,
            'tanggal_bergabung' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function cuti(): HasMany
    {
        return $this->hasMany(Cuti::class);
    }

    public function lembarKerja(): HasMany
    {
        return $this->hasMany(LembarKerja::class);
    }

    public function logAbsensiMesin(): HasMany
    {
        return $this->hasMany(LogAbsensiMesin::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', StatusPjlp::AKTIF);
    }

    public function scopeUnit($query, $unit)
    {
        return $query->where('unit', $unit);
    }

    public function scopeForKoordinator($query, User $user)
    {
        if ($user->isKoordinator() && $user->unit && $user->unit !== UnitType::ALL) {
            return $query->where('unit', $user->unit);
        }
        return $query;
    }

    public function getFotoUrlAttribute(): ?string
    {
        if ($this->foto) {
            return asset('storage/pjlp/' . $this->foto);
        }
        return null;
    }
}
