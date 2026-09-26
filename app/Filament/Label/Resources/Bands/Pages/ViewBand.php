<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Bands\Pages;

use App\Filament\Label\Resources\Bands\BandResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBand extends ViewRecord
{
    protected static string $resource = BandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
