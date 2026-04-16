<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class LogbookLimbahFoto extends Model
{
    protected $table = 'logbook_limbah_foto';

    protected $fillable = [
        'logbook_id',
        'kategori',
        'path',
    ];

    public function logbook(): BelongsTo
    {
        return $this->belongsTo(LogbookLimbah::class, 'logbook_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
