<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    protected $table      = 'app_settings';
    protected $primaryKey = 'key';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = ['key', 'value', 'description'];

    /** Ambil nilai setting, dengan cache 60 detik. */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("app_setting:{$key}", 60, function () use ($key, $default) {
            $setting = static::find($key);
            return $setting?->value ?? $default;
        });
    }

    /** Simpan nilai dan hapus cache. */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("app_setting:{$key}");
    }
}
