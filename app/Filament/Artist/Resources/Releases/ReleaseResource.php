<?php

declare(strict_types=1);

namespace App\Filament\Artist\Resources\Releases;

use App\Filament\Artist\Resources\Releases\Pages\ListReleases;
use App\Filament\Artist\Resources\Releases\Pages\ViewRelease;
use App\Filament\Artist\Resources\Releases\Schemas\ReleaseInfolist;
use App\Filament\Artist\Resources\Releases\Tables\ReleasesTable;
use App\Models\Artist;
use App\Models\Release;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Read-only view, in the artist portal, of the releases of every band the
 * current artist is (or was) a member of.
 *
 * Releases have no direct ownership relationship to an artist, so Filament's
 * automatic tenant scoping is disabled and replaced by an explicit scope:
 *  1. The query is restricted to the current artist's bands (fail closed without a tenant).
 *  2. Drafts are excluded at query level (label-internal work in progress).
 *  3. ReleasePolicy re-checks every record; write abilities are hard-disabled here.
 */
class ReleaseResource extends Resource
{
    protected static ?string $model = Release::class;

    protected static bool $isScopedToTenant = false;

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
        $artist = Filament::getTenant();

        if (! $artist instanceof Artist) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->creditedToArtist($artist)
            ->visibleToArtists()
            ->with('band');
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
