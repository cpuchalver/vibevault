<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BandType;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Relations\Pivot;
use LogicException;

/**
 * Membership of an artist in a band (role and tenure).
 *
 * Integrity guards, enforced on every pivot write independently of the UI:
 *  - an artist can only join bands of their own label;
 *  - a solo project has exactly one member;
 *  - a band always keeps at least one member (the last one cannot be removed).
 */
#[Table('artist_band')]
class BandMembership extends Pivot
{
    public $incrementing = true;

    protected static function booted(): void
    {
        static::saving(function (BandMembership $membership): void {
            $bandLabelId = Band::query()
                ->withoutGlobalScopes()
                ->whereKey($membership->band_id)
                ->value('label_id');

            $artistLabelId = Artist::query()
                ->withoutGlobalScopes()
                ->whereKey($membership->artist_id)
                ->value('label_id');

            if ($bandLabelId === null || (int) $bandLabelId !== (int) $artistLabelId) {
                throw new LogicException('An artist can only be a member of a band of the same label.');
            }

            if ($membership->joined_on && $membership->left_on && $membership->left_on->lt($membership->joined_on)) {
                throw new LogicException('A membership cannot end before it starts.');
            }

            $isSolo = Band::query()
                ->withoutGlobalScopes()
                ->whereKey($membership->band_id)
                ->where('type', BandType::Solo)
                ->exists();

            $hasOtherMembers = static::query()
                ->where('band_id', $membership->band_id)
                ->where('artist_id', '!=', $membership->artist_id)
                ->exists();

            if ($isSolo && $hasOtherMembers) {
                throw new LogicException('A solo project has exactly one member.');
            }
        });

        static::deleting(function (BandMembership $membership): void {
            $remainingMembers = static::query()
                ->where('band_id', $membership->band_id)
                ->where('artist_id', '!=', $membership->artist_id)
                ->count();

            if ($remainingMembers === 0) {
                throw new LogicException('A band must keep at least one member.');
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
