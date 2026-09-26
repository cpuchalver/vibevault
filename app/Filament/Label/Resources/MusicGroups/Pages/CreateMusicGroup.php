<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\MusicGroups\Pages;

use App\Filament\Label\Resources\MusicGroups\MusicGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMusicGroup extends CreateRecord
{
    protected static string $resource = MusicGroupResource::class;
}
