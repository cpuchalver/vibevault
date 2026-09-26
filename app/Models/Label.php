<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\GeneratesSlug;
use App\Policies\LabelPolicy;
use Database\Factories\LabelFactory;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A music label: the top-level tenant of the platform.
 */
#[UsePolicy(LabelPolicy::class)]
#[Fillable(['name', 'legal_name', 'country', 'contact_email'])]
class Label extends Model implements HasName
{
    use GeneratesSlug;

    /** @use HasFactory<LabelFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @return BelongsToMany<User, $this, LabelMembership>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(LabelMembership::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Artist, $this>
     */
    public function artists(): HasMany
    {
        return $this->hasMany(Artist::class);
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
