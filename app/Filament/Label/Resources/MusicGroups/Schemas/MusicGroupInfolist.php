<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\MusicGroups\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MusicGroupInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Groupe'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')->label(__('Nom')),
                        TextEntry::make('type')->label(__('Type de formation'))->badge(),
                        TextEntry::make('country')->label(__('Pays'))->placeholder('—'),
                        TextEntry::make('isni')->label(__('ISNI'))->placeholder('—'),
                        TextEntry::make('formed_on')->label(__('Formation'))->date()->placeholder('—'),
                        TextEntry::make('disbanded_on')->label(__('Séparation'))->date()->placeholder('—'),
                        TextEntry::make('biography')->label(__('Biographie'))->placeholder('—')->columnSpanFull(),
                    ]),
            ]);
    }
}
