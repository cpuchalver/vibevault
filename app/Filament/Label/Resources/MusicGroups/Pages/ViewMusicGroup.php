<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\MusicGroups\Pages;

use App\Filament\Label\Resources\MusicGroups\MusicGroupResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMusicGroup extends ViewRecord
{
    protected static string $resource = MusicGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
