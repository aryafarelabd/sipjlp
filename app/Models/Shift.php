<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'jam_mulai',
        'bi',
        'ai',
        'jam_selesai',
        'bo',
        'ao',
        'toleransi_terlambat',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'jam_mulai'  => 'datetime:H:i',
            'bi'         => 'datetime:H:i',
            'ai'         => 'datetime:H:i',
            'jam_selesai'=> 'datetime:H:i',
            'bo'         => 'datetime:H:i',
            'ao'         => 'datetime:H:i',
            'is_active'  => 'boolean',
        ];
    }

    public function jadwal(): HasMany
    {
        return $this->hasMany(Jadwal::class);
    }

    public function absensi(): HasMany
    {
        return $this->hasMany(Absensi::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
