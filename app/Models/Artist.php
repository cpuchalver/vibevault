<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use App\Policies\ArtistPolicy;
use Database\Factories\ArtistFactory;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
     * @return HasMany<Release, $this>
     */
    public function releases(): HasMany
    {
        return $this->hasMany(Release::class);
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
}
