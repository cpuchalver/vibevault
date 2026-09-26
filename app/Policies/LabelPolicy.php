<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\LabelPermission;
use App\Models\Label;
use App\Models\User;

class LabelPolicy
{
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Label $label): bool
    {
        return $user->belongsToLabel($label);
    }

    /**
     * Any existing label member may register an additional label (becoming its owner).
     */
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail() && $user->labels()->exists();
    }

    public function update(User $user, Label $label): bool
    {
        return $user->hasLabelPermission($label, LabelPermission::ManageLabel);
    }

    public function manageMembers(User $user, Label $label): bool
    {
        return $user->hasLabelPermission($label, LabelPermission::ManageMembers);
    }

    public function delete(User $user, Label $label): bool
    {
        return $user->hasLabelPermission($label, LabelPermission::ManageLabel);
    }

    public function restore(User $user, Label $label): bool
    {
        return false;
    }

    public function forceDelete(User $user, Label $label): bool
    {
        return false;
    }
}
