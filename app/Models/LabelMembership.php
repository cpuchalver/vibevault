<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LabelRole;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Membership of a user inside a label, carrying the user's role.
 *
 * @property LabelRole $role
 */
#[Table('label_user')]
class LabelMembership extends Pivot
{
    public $incrementing = true;

    protected function casts(): array
    {
        return [
            'role' => LabelRole::class,
        ];
    }
}
