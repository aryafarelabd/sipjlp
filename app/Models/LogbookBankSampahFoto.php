<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class LogbookBankSampahFoto extends Model
{
    protected $table = 'logbook_bank_sampah_foto';

    protected $fillable = ['logbook_bank_sampah_id', 'path'];

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
