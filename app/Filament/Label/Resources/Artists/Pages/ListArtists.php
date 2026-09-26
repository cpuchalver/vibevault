<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Artists\Pages;

use App\Filament\Label\Resources\Artists\ArtistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListArtists extends ListRecords
{
    protected static string $resource = ArtistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
