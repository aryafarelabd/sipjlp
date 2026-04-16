<?php

namespace App\Models;

use App\Enums\UnitType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'nip',
        'telegram_chat_id',
        'email',
        'password',
        'unit',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'unit' => UnitType::class,
            'is_active' => 'boolean',
        ];
    }

    public function pjlp(): HasOne
    {
        return $this->hasOne(Pjlp::class);
    }

    public function isPjlp(): bool
    {
        return $this->hasRole('pjlp');
    }

    public function isKoordinator(): bool
    {
        return $this->hasRole('koordinator');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isManajemen(): bool
    {
        return $this->hasRole('manajemen');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
