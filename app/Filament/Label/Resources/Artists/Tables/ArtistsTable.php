<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Artists\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ArtistsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount(['releases', 'users']))
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Nom de scène'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('country')
                    ->label(__('Pays'))
                    ->sortable(),
                TextColumn::make('releases_count')
                    ->label(__('Sorties'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('users_count')
                    ->label(__('Comptes portail'))
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Créé le'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
