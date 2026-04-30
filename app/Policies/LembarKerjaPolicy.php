<?php

namespace App\Policies;

use App\Models\LembarKerja;
use App\Models\User;

class LembarKerjaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAny(['lembar-kerja.view-self', 'lembar-kerja.view-unit', 'lembar-kerja.view-all']);
    }

    public function view(User $user, LembarKerja $lembarKerja): bool
    {
        if ($user->can('lembar-kerja.view-all')) {
            return true;
        }

        if ($user->can('lembar-kerja.view-unit')) {
            $pjlp = $lembarKerja->pjlp;
            if ($user->unit && $user->unit->value !== 'all') {
                return $pjlp->unit->value === $user->unit->value;
            }
            return true;
        }

        if ($user->can('lembar-kerja.view-self')) {
            return $lembarKerja->pjlp_id === $user->pjlp?->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->can('lembar-kerja.create') && $user->pjlp !== null;
    }

    public function validate(User $user, LembarKerja $lembarKerja): bool
    {
        if (!$user->can('lembar-kerja.validate')) {
            return false;
        }

        if ($user->hasRole('admin')) {
            return true;
        }

        if ($user->isKoordinator()) {
            $pjlp = $lembarKerja->pjlp;
            if ($user->unit && $user->unit->value !== 'all') {
                return $pjlp->unit->value === $user->unit->value;
            }
            return true;
        }

        return false;
    }
}
