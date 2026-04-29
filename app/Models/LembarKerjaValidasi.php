<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LembarKerjaValidasi extends Model
{
    use HasFactory;

    protected $table = 'lembar_kerja_validasi';

    protected $fillable = [
        'lembar_kerja_id',
        'validated_by',
        'validated_at',
        'catatan',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    public function lembarKerja(): BelongsTo
    {
        return $this->belongsTo(LembarKerja::class, 'lembar_kerja_id');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
