<?php

namespace App\Policies;

use App\Enums\StatusCuti;
use App\Enums\UnitType;
use App\Models\Cuti;
use App\Models\User;

class CutiPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAny(['cuti.view-self', 'cuti.view-unit', 'cuti.view-all']);
    }

    public function view(User $user, Cuti $cuti): bool
    {
        if ($user->can('cuti.view-all')) {
            return true;
        }

        if ($user->can('cuti.view-unit')) {
            $pjlp = $cuti->pjlp;

            // Danru hanya lihat cuti yang ditujukan kepadanya
            if ($user->hasRole('danru')) {
                return $cuti->danru_id === $user->pjlp?->id
                    || $cuti->pjlp_id === $user->pjlp?->id;
            }

            // Chief & koordinator: scope unit security
            if ($user->unit && $user->unit->value !== 'all') {
                return $pjlp->unit->value === $user->unit->value;
            }
            return true;
        }

        if ($user->can('cuti.view-self')) {
            return $cuti->pjlp_id === $user->pjlp?->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('cuti.create') && $user->pjlp !== null;
    }

    public function approve(User $user, Cuti $cuti): bool
    {
        if (!$user->can('cuti.approve')) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        // Danru: approve level menunggu_danru, hanya cuti yang ditujukan ke danru ini
        if ($user->hasRole('danru')) {
            return $cuti->status === StatusCuti::MENUNGGU_DANRU
                && $cuti->danru_id === $user->pjlp?->id;
        }

        // Chief: approve level menunggu_chief, unit security saja
        if ($user->hasRole('chief')) {
            return $cuti->status === StatusCuti::MENUNGGU_CHIEF
                && $cuti->pjlp?->unit === UnitType::SECURITY;
        }

        // Koordinator: approve menunggu (CS) atau menunggu_koordinator (security)
        if ($user->isKoordinator()) {
            $statusOk = in_array($cuti->status, [
                StatusCuti::MENUNGGU,
                StatusCuti::MENUNGGU_KOORDINATOR,
            ]);
            if (!$statusOk) {
                return false;
            }
            $pjlp = $cuti->pjlp;
            if ($user->unit && $user->unit->value !== 'all') {
                return $pjlp->unit->value === $user->unit->value;
            }
            return true;
        }

        return false;
    }
}
