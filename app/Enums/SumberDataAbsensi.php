<?php

namespace App\Enums;

enum SumberDataAbsensi: string
{
    case MESIN = 'mesin';
    case MANUAL = 'manual';
    case SELFIE = 'selfie';

    public function label(): string
    {
        return match($this) {
            self::MESIN   => 'Mesin Absensi',
            self::MANUAL  => 'Input Manual',
            self::SELFIE  => 'Data Absensi Lama',
        };
    }
}
