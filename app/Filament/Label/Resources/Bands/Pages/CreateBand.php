<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Bands\Pages;

use App\Filament\Label\Resources\Bands\BandResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBand extends CreateRecord
{
    protected static string $resource = BandResource::class;
}
