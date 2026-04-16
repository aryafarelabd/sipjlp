<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LogbookHepafilterFoto extends Model
{
    protected $table = 'logbook_hepafilter_foto';

    protected $fillable = ['logbook_hepafilter_id', 'path'];

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
