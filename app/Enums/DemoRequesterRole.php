<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum DemoRequesterRole: string implements HasLabel
{
    case Label = 'label';
    case Publisher = 'publisher';
    case Manager = 'manager';
    case Distributor = 'distributor';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::Label => 'Label',
            self::Publisher => 'Éditeur musical',
            self::Manager => 'Management d’artistes',
            self::Distributor => 'Distributeur',
            self::Other => 'Autre',
        };
    }
}
