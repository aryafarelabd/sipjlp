<?php

namespace App\Enums;

enum StatusCuti: string
{
    case MENUNGGU             = 'menunggu';             // CS: langsung ke koordinator (legacy)
    case MENUNGGU_DANRU       = 'menunggu_danru';       // Security anggota → danru
    case MENUNGGU_CHIEF       = 'menunggu_chief';       // Security → chief
    case MENUNGGU_KOORDINATOR = 'menunggu_koordinator'; // Security → koordinator (final)
    case DISETUJUI            = 'disetujui';
    case DITOLAK              = 'ditolak';

    public function label(): string
    {
        return match($this) {
            self::MENUNGGU             => 'Menunggu Koordinator',
            self::MENUNGGU_DANRU       => 'Menunggu Danru',
            self::MENUNGGU_CHIEF       => 'Menunggu Chief',
            self::MENUNGGU_KOORDINATOR => 'Menunggu Koordinator',
            self::DISETUJUI            => 'Disetujui',
            self::DITOLAK              => 'Ditolak',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::MENUNGGU,
            self::MENUNGGU_DANRU,
            self::MENUNGGU_CHIEF,
            self::MENUNGGU_KOORDINATOR => 'warning',
            self::DISETUJUI            => 'success',
            self::DITOLAK              => 'danger',
        };
    }

    public function isPending(): bool
    {
        return in_array($this, [
            self::MENUNGGU,
            self::MENUNGGU_DANRU,
            self::MENUNGGU_CHIEF,
            self::MENUNGGU_KOORDINATOR,
        ]);
    }
}
