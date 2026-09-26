<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum BandType: string implements HasLabel
{
    case Solo = 'solo';
    case Band = 'band';
    case Duo = 'duo';
    case Collective = 'collective';
    case Orchestra = 'orchestra';
    case Choir = 'choir';

    public function getLabel(): string
    {
        return match ($this) {
            self::Solo => __('Projet solo'),
            self::Band => __('Groupe'),
            self::Duo => __('Duo'),
            self::Collective => __('Collectif'),
            self::Orchestra => __('Orchestre / ensemble'),
            self::Choir => __('Chœur'),
        };
    }
}
