<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\MusicGroupType;
use App\Models\Concerns\GeneratesSlug;
use App\Policies\MusicGroupPolicy;
use Database\Factories\MusicGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A musical group (band, duo, collective, orchestra…) of a label, made of several artists.
 *
 * Named after schema.org `MusicGroup` to cover every kind of ensemble, not just bands.
 * `label_id` is not fillable: it is set from the tenant.
 */
#[UsePolicy(MusicGroupPolicy::class)]
#[Fillable(['name', 'type', 'country', 'isni', 'formed_on', 'disbanded_on', 'biography'])]
class MusicGroup extends Model
{
    use GeneratesSlug;

    /** @use HasFactory<MusicGroupFactory> */
    use HasFactory;

    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'type' => MusicGroupType::class,
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
     * @return BelongsToMany<Artist, $this, MusicGroupMembership>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class)
            ->using(MusicGroupMembership::class)
            ->withPivot(['id', 'role', 'joined_on', 'left_on'])
            ->withTimestamps();
    }
}
