<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Fills a globally unique `slug` from `name` on creation.
 *
 * Slugs are used in panel URLs (tenant identifiers), so they must never collide
 * across tenants. Trashed records are included in the uniqueness check.
 *
 * @mixin Model
 */
trait GeneratesSlug
{
    public static function bootGeneratesSlug(): void
    {
        static::creating(function (Model $model): void {
            if (filled($model->getAttribute('slug'))) {
                return;
            }

            $model->setAttribute('slug', static::uniqueSlugFor((string) $model->getAttribute('name')));
        });
    }

    protected static function uniqueSlugFor(string $name): string
    {
        $base = Str::slug($name) ?: Str::lower(Str::random(8));
        $slug = $base;

        while (static::query()->withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = "{$base}-".Str::lower(Str::random(5));
        }

        return $slug;
    }
}
