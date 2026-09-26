<?php

declare(strict_types=1);

namespace App\Filament\Artist\Resources\Releases\Pages;

use App\Filament\Artist\Resources\Releases\ReleaseResource;
use Filament\Resources\Pages\ListRecords;

class ListReleases extends ListRecords
{
    protected static string $resource = ReleaseResource::class;
}
