<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use App\Policies\ArtistPolicy;
use Database\Factories\ArtistFactory;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An artist signed to a label. Also the tenant of the artist portal.
 *
 * `label_id` is deliberately NOT fillable: it is always set from the current
 * tenant through the `label` relationship, never from user input.
 */
#[UsePolicy(ArtistPolicy::class)]
#[Fillable(['name', 'legal_name', 'country', 'isni', 'biography'])]
class Artist extends Model implements HasName
{
    use GeneratesSlug;

    /** @use HasFactory<ArtistFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @return BelongsTo<Label, $this>
     */
    public function label(): BelongsTo
    {
        return $this->belongsTo(Label::class);
    }

    /**
     * Portal accounts linked to this artist.
     *
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Bands this artist plays in (a single artist can belong to several bands).
     *
     * @return BelongsToMany<Band, $this, BandMembership>
     */
    public function bands(): BelongsToMany
    {
        return $this->belongsToMany(Band::class)
            ->using(BandMembership::class)
            ->withPivot(['id', 'role', 'joined_on', 'left_on'])
            ->withTimestamps();
    }

    /**
     * Releases credited to any band this artist is (or was) a member of.
     *
     * @return Builder<Release>
     */
    public function releasesQuery(): Builder
    {
        return Release::query()->creditedToArtist($this);
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
}
