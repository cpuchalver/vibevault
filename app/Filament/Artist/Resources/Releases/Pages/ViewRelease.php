<?php

declare(strict_types=1);

namespace App\Filament\Artist\Resources\Releases\Pages;

use App\Filament\Artist\Resources\Releases\ReleaseResource;
use Filament\Resources\Pages\ViewRecord;

class ViewRelease extends ViewRecord
{
    protected static string $resource = ReleaseResource::class;
}
