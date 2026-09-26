<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\LabelPermission;
use App\Models\Band;
use App\Models\User;

/**
 * Groups are part of the artist roster: same permissions as artists.
 */
class BandPolicy
{
    use ResolvesCurrentTenant;

    public function viewAny(User $user): bool
    {
        $label = $this->currentLabel();

        return $label !== null && $user->hasLabelPermission($label, LabelPermission::ViewCatalog);
    }

    public function view(User $user, Band $band): bool
    {
        return $user->hasLabelPermission($band->label_id, LabelPermission::ViewCatalog);
    }

    public function create(User $user): bool
    {
        $label = $this->currentLabel();

        return $label !== null && $user->hasLabelPermission($label, LabelPermission::ManageArtists);
    }

    public function update(User $user, Band $band): bool
    {
        return $user->hasLabelPermission($band->label_id, LabelPermission::ManageArtists);
    }

    /**
     * Attach, detach and edit memberships (role, tenure).
     */
    public function manageMembers(User $user, Band $band): bool
    {
        return $user->hasLabelPermission($band->label_id, LabelPermission::ManageArtists);
    }

    public function delete(User $user, Band $band): bool
    {
        return $user->hasLabelPermission($band->label_id, LabelPermission::ManageArtists);
    }

    public function deleteAny(User $user): bool
    {
        return $this->create($user);
    }

    public function restore(User $user, Band $band): bool
    {
        return $user->hasLabelPermission($band->label_id, LabelPermission::ManageArtists);
    }

    public function restoreAny(User $user): bool
    {
        return $this->create($user);
    }

    public function forceDelete(User $user, Band $band): bool
    {
        return $user->hasLabelPermission($band->label_id, LabelPermission::ManageLabel);
    }

    public function forceDeleteAny(User $user): bool
    {
        $label = $this->currentLabel();

        return $label !== null && $user->hasLabelPermission($label, LabelPermission::ManageLabel);
    }
}
