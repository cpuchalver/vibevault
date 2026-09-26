<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ReleaseStatus;
use App\Enums\ReleaseType;
use App\Policies\ReleasePolicy;
use Database\Factories\ReleaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use LogicException;

/**
 * A release (single, EP, album…) owned by a label and credited to one of its artists.
 *
 * `label_id` is not fillable: it is set from the tenant. An integrity guard
 * refuses to persist a release whose artist belongs to another label.
 */
#[UsePolicy(ReleasePolicy::class)]
#[Fillable(['artist_id', 'title', 'type', 'status', 'upc', 'catalog_number', 'release_date'])]
class Release extends Model
{
    /** @use HasFactory<ReleaseFactory> */
    use HasFactory;

    use SoftDeletes;

    protected static function booted(): void
    {
        static::saving(function (Release $release): void {
            $artistLabelId = Artist::query()
                ->withoutGlobalScopes()
                ->whereKey($release->artist_id)
                ->value('label_id');

            if ($artistLabelId === null || (int) $artistLabelId !== (int) $release->label_id) {
                throw new LogicException('A release must belong to the same label as its artist.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'type' => ReleaseType::class,
            'status' => ReleaseStatus::class,
            'release_date' => 'date',
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
     * @return BelongsTo<Artist, $this>
     */
    public function artist(): BelongsTo
    {
        return $this->belongsTo(Artist::class);
    }

    /**
     * @param  Builder<Release>  $query
     */
    #[Scope]
    protected function visibleToArtists(Builder $query): void
    {
        $query->whereIn('status', ReleaseStatus::visibleToArtists());
    }

    public function isVisibleToArtists(): bool
    {
        return in_array($this->status, ReleaseStatus::visibleToArtists(), true);
    }
}
