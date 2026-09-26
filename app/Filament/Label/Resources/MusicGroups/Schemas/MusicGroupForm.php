<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\MusicGroups\Schemas;

use App\Enums\MusicGroupType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class MusicGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Groupe'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Nom'))
                            ->required()
                            ->maxLength(120),
                        Select::make('type')
                            ->label(__('Type de formation'))
                            ->options(MusicGroupType::class)
                            ->default(MusicGroupType::Band)
                            ->required(),
                        TextInput::make('country')
                            ->label(__('Pays (ISO 3166-1 alpha-2)'))
                            ->length(2)
                            ->regex('/^[A-Z]{2}$/')
                            ->dehydrateStateUsing(fn (?string $state): ?string => $state ? strtoupper($state) : null),
                        TextInput::make('isni')
                            ->label(__('ISNI'))
                            ->regex('/^\d{15}[\dX]$/')
                            ->helperText(__('16 caractères, sans espaces.')),
                        DatePicker::make('formed_on')
                            ->label(__('Date de formation')),
                        DatePicker::make('disbanded_on')
                            ->label(__('Date de séparation'))
                            ->afterOrEqual(fn (Get $get): ?string => $get('formed_on') ?: null),
                        Textarea::make('biography')
                            ->label(__('Biographie'))
                            ->rows(5)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
