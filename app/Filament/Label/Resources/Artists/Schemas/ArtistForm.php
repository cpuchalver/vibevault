<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Artists\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ArtistForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Identité artistique'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Nom de scène'))
                            ->required()
                            ->maxLength(120),
                        TextInput::make('country')
                            ->label(__('Pays (ISO 3166-1 alpha-2)'))
                            ->length(2)
                            ->regex('/^[A-Z]{2}$/')
                            ->dehydrateStateUsing(fn (?string $state): ?string => $state ? strtoupper($state) : null),
                        Toggle::make('create_solo_project')
                            ->label(__('Créer aussi son projet solo'))
                            ->helperText(__('Une sortie est toujours liée à un groupe : le projet solo permet de publier sous le nom de l\'artiste.'))
                            ->default(true)
                            ->dehydrated(false)
                            ->visibleOn('create')
                            ->columnSpanFull(),
                        Textarea::make('biography')
                            ->label(__('Biographie'))
                            ->rows(5)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('Données légales'))
                    ->description(__('Données personnelles (RGPD) : visibles uniquement par les membres autorisés du label.'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('legal_name')
                            ->label(__('Nom légal'))
                            ->maxLength(160),
                        TextInput::make('isni')
                            ->label(__('ISNI'))
                            ->regex('/^\d{15}[\dX]$/')
                            ->helperText(__('16 caractères, sans espaces.')),
                    ]),
            ]);
    }
}
