<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Releases\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReleaseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Sortie'))
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')->label(__('Titre')),
                        TextEntry::make('band.name')->label(__('Groupe')),
                        TextEntry::make('type')->label(__('Format'))->badge(),
                        TextEntry::make('status')->label(__('Statut'))->badge(),
                        TextEntry::make('release_date')->label(__('Date de sortie'))->date()->placeholder('—'),
                        TextEntry::make('upc')->label(__('UPC / EAN'))->placeholder('—')->copyable(),
                        TextEntry::make('catalog_number')->label(__('Référence catalogue'))->placeholder('—'),
                    ]),
            ]);
    }
}
