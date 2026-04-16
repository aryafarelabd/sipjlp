<?php

namespace App\Enums;

enum StatusAbsensi: string
{
    case HADIR = 'hadir';
    case TERLAMBAT = 'terlambat';
    case ALPHA = 'alpha';
    case IZIN = 'izin';
    case CUTI = 'cuti';
    case SAKIT = 'sakit';
    case LIBUR = 'libur';

    public function label(): string
    {
        return match($this) {
            self::HADIR     => 'Hadir',
            self::TERLAMBAT => 'Terlambat',
            self::ALPHA     => 'Alpha',
            self::IZIN      => 'Izin',
            self::CUTI      => 'Cuti',
            self::SAKIT     => 'Sakit',
            self::LIBUR     => 'Libur',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::HADIR     => 'success',
            self::TERLAMBAT => 'warning',
            self::ALPHA     => 'danger',
            self::IZIN      => 'info',
            self::CUTI      => 'primary',
            self::SAKIT     => 'secondary',
            self::LIBUR     => 'secondary',
        };
    }
}
