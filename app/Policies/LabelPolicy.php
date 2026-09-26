<?php

declare(strict_types=1);

namespace App\Policies;

use App\Billing\PlanCatalog;
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
     * Any existing label member may register an additional label (becoming its
     * owner), which must be subscribed to: only possible when plans are sold online.
     */
    public function create(User $user): bool
    {
        return $user->hasVerifiedEmail()
            && $user->labels()->exists()
            && app(PlanCatalog::class)->selfServeOptions() !== [];
    }

    public function update(User $user, Label $label): bool
    {
        return $user->hasLabelPermission($label, LabelPermission::ManageLabel);
    }

    /**
     * Subscription, payment method and invoices (Stripe Checkout and portal).
     */
    public function manageBilling(User $user, Label $label): bool
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
