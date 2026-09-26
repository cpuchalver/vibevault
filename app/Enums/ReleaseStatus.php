<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum ReleaseStatus: string implements HasColor, HasLabel
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Released = 'released';
    case Withdrawn = 'withdrawn';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft => __('Brouillon'),
            self::Scheduled => __('Programmée'),
            self::Released => __('Publiée'),
            self::Withdrawn => __('Retirée'),
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Scheduled => 'info',
            self::Released => 'success',
            self::Withdrawn => 'danger',
        };
    }

    /**
     * Artists only see releases that have left the draft stage.
     *
     * @return list<self>
     */
    public static function visibleToArtists(): array
    {
        return [self::Scheduled, self::Released, self::Withdrawn];
    }
}
