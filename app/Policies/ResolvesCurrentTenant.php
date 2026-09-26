<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Artist;
use App\Models\Label;
use Filament\Facades\Filament;

/**
 * Policy abilities without a record (viewAny, create) are evaluated against
 * the tenant of the current panel request. Outside a tenant context they
 * resolve to null and the ability is denied (fail closed).
 */
trait ResolvesCurrentTenant
{
    protected function currentLabel(): ?Label
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Label ? $tenant : null;
    }

    protected function currentArtist(): ?Artist
    {
        $tenant = Filament::getTenant();

        return $tenant instanceof Artist ? $tenant : null;
    }
}
