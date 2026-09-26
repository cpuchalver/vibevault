<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CatalogueSize: string implements HasLabel
{
    case Small = 'small';
    case Medium = 'medium';
    case Large = 'large';
    case VeryLarge = 'very_large';

    public function getLabel(): string
    {
        return match ($this) {
            self::Small => 'Moins de 100 titres',
            self::Medium => '100 à 1 000 titres',
            self::Large => '1 000 à 10 000 titres',
            self::VeryLarge => 'Plus de 10 000 titres',
        };
    }
}
