<?php

declare(strict_types=1);

namespace App\Filament\Artist\Resources\Releases;

use App\Filament\Artist\Resources\Releases\Pages\ListReleases;
use App\Filament\Artist\Resources\Releases\Pages\ViewRelease;
use App\Filament\Artist\Resources\Releases\Schemas\ReleaseInfolist;
use App\Filament\Artist\Resources\Releases\Tables\ReleasesTable;
use App\Models\Release;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only view of the current artist's releases in the artist portal.
 *
 * Three layers of isolation:
 *  1. Filament tenancy scopes the query to the current artist (`artist` ownership relationship).
 *  2. Drafts are excluded at query level (label-internal work in progress).
 *  3. ReleasePolicy re-checks every record; write abilities are hard-disabled here.
 */
class ReleaseResource extends Resource
{
    protected static ?string $model = Release::class;

    protected static bool $isScopedToTenant = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMusicalNote;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModelLabel(): string
    {
        return __('sortie');
    }

    public static function getPluralModelLabel(): string
    {
        return __('mes sorties');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->visibleToArtists();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReleaseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReleasesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReleases::route('/'),
            'view' => ViewRelease::route('/{record}'),
        ];
    }
}
