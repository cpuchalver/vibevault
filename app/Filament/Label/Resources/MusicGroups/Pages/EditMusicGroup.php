<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\MusicGroups\Pages;

use App\Filament\Label\Resources\MusicGroups\MusicGroupResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMusicGroup extends EditRecord
{
    protected static string $resource = MusicGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
