<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Artists\Pages;

use App\Filament\Label\Resources\Artists\ArtistResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewArtist extends ViewRecord
{
    protected static string $resource = ArtistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
