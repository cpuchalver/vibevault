<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ReleaseType: string implements HasLabel
{
    case Single = 'single';
    case Ep = 'ep';
    case Album = 'album';
    case Compilation = 'compilation';

    public function getLabel(): string
    {
        return match ($this) {
            self::Single => __('Single'),
            self::Ep => __('EP'),
            self::Album => __('Album'),
            self::Compilation => __('Compilation'),
        };
    }
}
