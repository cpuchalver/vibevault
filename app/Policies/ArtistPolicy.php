<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\LabelPermission;
use App\Models\Artist;
use App\Models\User;

class ArtistPolicy
{
    use ResolvesCurrentTenant;

    public function viewAny(User $user): bool
    {
        $label = $this->currentLabel();

        return $label !== null && $user->hasLabelPermission($label, LabelPermission::ViewCatalog);
    }

    public function view(User $user, Artist $artist): bool
    {
        return $user->hasLabelPermission($artist->label_id, LabelPermission::ViewCatalog)
            || $user->isLinkedToArtist($artist);
    }

    public function create(User $user): bool
    {
        $label = $this->currentLabel();

        return $label !== null && $user->hasLabelPermission($label, LabelPermission::ManageArtists);
    }

    public function update(User $user, Artist $artist): bool
    {
        return $user->hasLabelPermission($artist->label_id, LabelPermission::ManageArtists);
    }

    public function delete(User $user, Artist $artist): bool
    {
        return $user->hasLabelPermission($artist->label_id, LabelPermission::ManageArtists);
    }

    public function deleteAny(User $user): bool
    {
        return $this->create($user);
    }

    public function restore(User $user, Artist $artist): bool
    {
        return $user->hasLabelPermission($artist->label_id, LabelPermission::ManageArtists);
    }

    public function restoreAny(User $user): bool
    {
        return $this->create($user);
    }

    public function forceDelete(User $user, Artist $artist): bool
    {
        return $user->hasLabelPermission($artist->label_id, LabelPermission::ManageLabel);
    }

    public function forceDeleteAny(User $user): bool
    {
        $label = $this->currentLabel();

        return $label !== null && $user->hasLabelPermission($label, LabelPermission::ManageLabel);
    }

    /**
     * Link / unlink artist portal accounts.
     */
    public function managePortalAccess(User $user, Artist $artist): bool
    {
        return $user->hasLabelPermission($artist->label_id, LabelPermission::ManageArtists);
    }
}
