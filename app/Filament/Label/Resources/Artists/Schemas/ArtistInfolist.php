<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Artists\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArtistInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identité artistique'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')->label(__('Nom de scène')),
                        TextEntry::make('country')->label(__('Pays'))->placeholder('—'),
                        TextEntry::make('musicGroups.name')->label(__('Groupes'))->badge()->placeholder('—'),
                        TextEntry::make('biography')->label(__('Biographie'))->placeholder('—')->columnSpanFull(),
                    ]),
                Section::make(__('Données légales'))
                    ->columns(2)
                    ->schema([
                        TextEntry::make('legal_name')->label(__('Nom légal'))->placeholder('—'),
                        TextEntry::make('isni')->label(__('ISNI'))->placeholder('—'),
                    ]),
            ]);
    }
}
