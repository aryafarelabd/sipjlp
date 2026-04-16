<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogbookB3Foto extends Model
{
    protected $table = 'logbook_b3_foto';

    protected $fillable = [
        'logbook_b3_id',
        'kategori',
        'path',
    ];

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(LogbookB3::class, 'logbook_b3_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
