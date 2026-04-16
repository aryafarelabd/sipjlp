<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PatrolInspeksiFoto extends Model
{
    protected $table = 'patrol_inspeksi_foto';

    protected $fillable = ['patrol_inspeksi_id', 'seksi', 'path'];

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
