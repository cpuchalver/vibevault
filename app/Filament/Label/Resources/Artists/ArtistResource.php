<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Artists;

use App\Filament\Label\Resources\Artists\Pages\CreateArtist;
use App\Filament\Label\Resources\Artists\Pages\EditArtist;
use App\Filament\Label\Resources\Artists\Pages\ListArtists;
use App\Filament\Label\Resources\Artists\Pages\ViewArtist;
use App\Filament\Label\Resources\Artists\Schemas\ArtistForm;
use App\Filament\Label\Resources\Artists\Schemas\ArtistInfolist;
use App\Filament\Label\Resources\Artists\Tables\ArtistsTable;
use App\Models\Artist;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * Artists of the current label. Scoped by Filament tenancy (`label` ownership
 * relationship) and authorized by ArtistPolicy.
 */
class ArtistResource extends Resource
{
    protected static ?string $model = Artist::class;

    protected static bool $isScopedToTenant = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMicrophone;

    protected static string|UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('artiste');
    }

    public static function getPluralModelLabel(): string
    {
        return __('artistes');
    }

    public static function form(Schema $schema): Schema
    {
        return ArtistForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ArtistInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ArtistsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListArtists::route('/'),
            'create' => CreateArtist::route('/create'),
            'view' => ViewArtist::route('/{record}'),
            'edit' => EditArtist::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
