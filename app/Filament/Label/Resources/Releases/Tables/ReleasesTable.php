<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Releases\Tables;

use App\Enums\ReleaseStatus;
use App\Enums\ReleaseType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReleasesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('band'))
            ->defaultSort('release_date', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->label(__('Titre'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('band.name')
                    ->label(__('Groupe'))
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
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('catalog_number')
                    ->label(__('Réf. catalogue'))
                    ->searchable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('Statut'))
                    ->options(ReleaseStatus::class),
                SelectFilter::make('type')
                    ->label(__('Format'))
                    ->options(ReleaseType::class),
                SelectFilter::make('band')
                    ->label(__('Groupe'))
                    ->relationship(
                        'band',
                        'name',
                        fn (Builder $query): Builder => $query->whereBelongsTo(Filament::getTenant()),
                    )
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
