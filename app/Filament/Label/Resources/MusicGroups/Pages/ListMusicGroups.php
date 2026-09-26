<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\MusicGroups\Pages;

use App\Filament\Label\Resources\MusicGroups\MusicGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMusicGroups extends ListRecords
{
    protected static string $resource = MusicGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
