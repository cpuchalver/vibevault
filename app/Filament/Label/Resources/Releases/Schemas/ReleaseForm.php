<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Releases\Schemas;

use App\Enums\ReleaseStatus;
use App\Enums\ReleaseType;
use App\Models\Artist;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ReleaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Sortie'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label(__('Titre'))
                            ->required()
                            ->maxLength(200),
                        Select::make('artist_id')
                            ->label(__('Artiste principal'))
                            ->relationship(
                                name: 'artist',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->whereBelongsTo(Filament::getTenant()),
                            )
                            // Defense in depth: never trust the submitted id, re-check it belongs to this label.
                            ->scopedExists(
                                model: Artist::class,
                                column: 'id',
                                modifyQueryUsing: fn (Builder $query): Builder => $query
                                    ->whereBelongsTo(Filament::getTenant())
                                    ->whereNull('deleted_at'),
                            )
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('type')
                            ->label(__('Format'))
                            ->options(ReleaseType::class)
                            ->required(),
                        Select::make('status')
                            ->label(__('Statut'))
                            ->options(ReleaseStatus::class)
                            ->default(ReleaseStatus::Draft)
                            ->required(),
                        DatePicker::make('release_date')
                            ->label(__('Date de sortie')),
                    ]),
                Section::make(__('Identifiants'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('upc')
                            ->label(__('UPC / EAN'))
                            ->regex('/^\d{12,13}$/')
                            ->scopedUnique(modifyQueryUsing: fn (Builder $query): Builder => $query
                                ->whereBelongsTo(Filament::getTenant())
                                ->withTrashed()),
                        TextInput::make('catalog_number')
                            ->label(__('Référence catalogue'))
                            ->maxLength(50)
                            ->scopedUnique(modifyQueryUsing: fn (Builder $query): Builder => $query
                                ->whereBelongsTo(Filament::getTenant())
                                ->withTrashed()),
                    ]),
            ]);
    }
}
