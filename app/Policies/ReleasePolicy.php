<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\LabelPermission;
use App\Models\Release;
use App\Models\User;

class ReleasePolicy
{
    use ResolvesCurrentTenant;

    public function viewAny(User $user): bool
    {
        if ($label = $this->currentLabel()) {
            return $user->hasLabelPermission($label, LabelPermission::ViewCatalog);
        }

        if ($artist = $this->currentArtist()) {
            return $user->isLinkedToArtist($artist);
        }

        return false;
    }

    public function view(User $user, Release $release): bool
    {
        if ($user->hasLabelPermission($release->label_id, LabelPermission::ViewCatalog)) {
            return true;
        }

        return $user->isLinkedToArtist($release->artist_id) && $release->isVisibleToArtists();
    }

    public function create(User $user): bool
    {
        $label = $this->currentLabel();

        return $label !== null && $user->hasLabelPermission($label, LabelPermission::ManageCatalog);
    }

    public function update(User $user, Release $release): bool
    {
        return $user->hasLabelPermission($release->label_id, LabelPermission::ManageCatalog);
    }

    public function delete(User $user, Release $release): bool
    {
        return $user->hasLabelPermission($release->label_id, LabelPermission::ManageCatalog);
    }

    public function deleteAny(User $user): bool
    {
        return $this->create($user);
    }

    public function restore(User $user, Release $release): bool
    {
        return $user->hasLabelPermission($release->label_id, LabelPermission::ManageCatalog);
    }

    public function restoreAny(User $user): bool
    {
        return $this->create($user);
    }

    public function forceDelete(User $user, Release $release): bool
    {
        return $user->hasLabelPermission($release->label_id, LabelPermission::ManageLabel);
    }

    public function forceDeleteAny(User $user): bool
    {
        $label = $this->currentLabel();

        return $label !== null && $user->hasLabelPermission($label, LabelPermission::ManageLabel);
    }
}
