<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BandType;
use App\Models\Concerns\GeneratesSlug;
use App\Policies\BandPolicy;
use Database\Factories\BandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A band of a label, made of several artists. Covers every kind of ensemble
 * (duo, collective, orchestra…) through {@see BandType}.
 *
 * `label_id` is not fillable: it is set from the tenant.
 */
#[UsePolicy(BandPolicy::class)]
#[Fillable(['name', 'type', 'country', 'isni', 'formed_on', 'disbanded_on', 'biography'])]
class Band extends Model
{
    use GeneratesSlug;

    /** @use HasFactory<BandFactory> */
    use HasFactory;

    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => BandType::class,
            'formed_on' => 'date',
            'disbanded_on' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Label, $this>
     */
    public function label(): BelongsTo
    {
        return $this->belongsTo(Label::class);
    }

    /**
     * @return BelongsToMany<Artist, $this, BandMembership>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class)
            ->using(BandMembership::class)
            ->withPivot(['id', 'role', 'joined_on', 'left_on'])
            ->withTimestamps();
    }
}
