<?php

declare(strict_types=1);

namespace App\Filament\Artist\Resources\Releases\Tables;

use App\Enums\ReleaseStatus;
use App\Enums\ReleaseType;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReleasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('release_date', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('Titre'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type')
                    ->label(__('Format'))
                    ->badge(),
                TextColumn::make('status')
                    ->label(__('Statut'))
                    ->badge(),
                TextColumn::make('release_date')
                    ->label(__('Date de sortie'))
                    ->date()
                    ->sortable(),
                TextColumn::make('upc')
                    ->label(__('UPC / EAN'))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Statut'))
                    ->options(array_combine(
                        array_map(fn (ReleaseStatus $status): string => $status->value, ReleaseStatus::visibleToArtists()),
                        array_map(fn (ReleaseStatus $status): string => $status->getLabel(), ReleaseStatus::visibleToArtists()),
                    )),
                SelectFilter::make('type')
                    ->label(__('Format'))
                    ->options(ReleaseType::class),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
