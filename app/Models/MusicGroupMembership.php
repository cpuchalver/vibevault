<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;
use LogicException;

/**
 * Membership of an artist in a music group (role and tenure).
 *
 * Integrity guard: an artist can only join groups of their own label. This is
 * enforced on every pivot write, independently of the UI.
 */
#[Table('artist_music_group')]
class MusicGroupMembership extends Pivot
{
    public $incrementing = true;

    protected static function booted(): void
    {
        static::saving(function (MusicGroupMembership $membership): void {
            $groupLabelId = MusicGroup::query()
                ->withoutGlobalScopes()
                ->whereKey($membership->music_group_id)
                ->value('label_id');

            $artistLabelId = Artist::query()
                ->withoutGlobalScopes()
                ->whereKey($membership->artist_id)
                ->value('label_id');

            if ($groupLabelId === null || (int) $groupLabelId !== (int) $artistLabelId) {
                throw new LogicException('An artist can only be a member of a group of the same label.');
            }

            if ($membership->joined_on && $membership->left_on && $membership->left_on->lt($membership->joined_on)) {
                throw new LogicException('A membership cannot end before it starts.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'joined_on' => 'date',
            'left_on' => 'date',
        ];
    }

    public function isActive(): bool
    {
        return $this->left_on === null || $this->left_on->isFuture();
    }
}
