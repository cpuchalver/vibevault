<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\Filament\ArtistPanelProvider;
use App\Providers\Filament\LabelPanelProvider;

return [
    AppServiceProvider::class,
    ArtistPanelProvider::class,
    LabelPanelProvider::class,
];
