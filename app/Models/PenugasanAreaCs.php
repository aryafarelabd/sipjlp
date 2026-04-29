<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PenugasanAreaCs extends Model
{
    use HasFactory;

    protected $table = 'penugasan_area_cs';

    protected $fillable = [
        'area_id',
        'pjlp_id',
        'shift_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_active',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_active' => 'boolean',
    ];

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

    public function getShiftNamaAttribute(): string
    {
        return $this->shift?->nama ?? '-';
    }

    public function isActiveOnDate($date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $date = \Carbon\Carbon::parse($date);

        if ($this->tanggal_mulai && $date->lt($this->tanggal_mulai)) {
            return false;
        }

        if ($this->tanggal_selesai && $date->gt($this->tanggal_selesai)) {
            return false;
        }

        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByArea($query, $areaId)
    {
        return $query->where('area_id', $areaId);
    }

    public function scopeByPjlp($query, $pjlpId)
    {
        return $query->where('pjlp_id', $pjlpId);
    }

    public function scopeByShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    public function scopeActiveOnDate($query, $date)
    {
        return $query->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->whereNull('tanggal_mulai')
                    ->orWhere('tanggal_mulai', '<=', $date);
            })
            ->where(function ($q) use ($date) {
                $q->whereNull('tanggal_selesai')
                    ->orWhere('tanggal_selesai', '>=', $date);
            });
    }
}
