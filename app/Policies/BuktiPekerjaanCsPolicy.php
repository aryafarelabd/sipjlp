<?php

namespace App\Policies;

use App\Models\BuktiPekerjaanCs;
use App\Models\JadwalKerjaCsBulanan;
use App\Models\User;

class BuktiPekerjaanCsPolicy
{
    /**
     * Apakah user boleh upload bukti untuk jadwal tertentu.
     * Hanya pjlp pemilik jadwal atau admin/koordinator unit yang sama.
     */
    public function upload(User $user, JadwalKerjaCsBulanan $jadwal): bool
    {
        if ($user->can('lembar-kerja-cs.view-all')) {
            return true;
        }

        if ($user->can('lembar-kerja-cs.view-unit')) {
            $pjlp = $jadwal->pjlp;
            if ($user->unit && $user->unit->value !== 'all') {
                return $pjlp->unit->value === $user->unit->value;
            }
            return true;
        }

        if ($user->can('lembar-kerja-cs.view-self')) {
            // Jadwal umum (pjlp_id null) boleh diupload siapa saja
            if (is_null($jadwal->pjlp_id)) {
                return true;
            }
            return $jadwal->pjlp_id === $user->pjlp?->id;
        }

        return false;
    }

    /**
     * Apakah user boleh memvalidasi bukti pekerjaan.
     */
    public function validate(User $user, BuktiPekerjaanCs $bukti): bool
    {
        if (!$user->can('lembar-kerja-cs.validate')) {
            return false;
        }

        if ($user->can('lembar-kerja-cs.view-all')) {
            return true;
        }

        if ($user->can('lembar-kerja-cs.view-unit')) {
            $pjlp = $bukti->pjlp;
            if ($user->unit && $user->unit->value !== 'all') {
                return $pjlp->unit->value === $user->unit->value;
            }
            return true;
        }

        return false;
    }
}
