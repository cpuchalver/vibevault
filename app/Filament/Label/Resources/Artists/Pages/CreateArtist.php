<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Artists\Pages;

use App\Filament\Label\Resources\Artists\ArtistResource;
use Filament\Resources\Pages\CreateRecord;

class CreateArtist extends CreateRecord
{
    protected static string $resource = ArtistResource::class;
}
