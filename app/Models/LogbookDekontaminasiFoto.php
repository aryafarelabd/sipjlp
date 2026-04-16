<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LogbookDekontaminasiFoto extends Model
{
    protected $table = 'logbook_dekontaminasi_foto';

    protected $fillable = ['logbook_dekontaminasi_id', 'path'];

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
