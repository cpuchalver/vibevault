<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Releases;

use App\Filament\Label\Resources\Releases\Pages\CreateRelease;
use App\Filament\Label\Resources\Releases\Pages\EditRelease;
use App\Filament\Label\Resources\Releases\Pages\ListReleases;
use App\Filament\Label\Resources\Releases\Pages\ViewRelease;
use App\Filament\Label\Resources\Releases\Schemas\ReleaseForm;
use App\Filament\Label\Resources\Releases\Schemas\ReleaseInfolist;
use App\Filament\Label\Resources\Releases\Tables\ReleasesTable;
use App\Models\Release;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * Label catalogue. Scoped to the current label by Filament tenancy
 * (`label` ownership relationship) and authorized by ReleasePolicy.
 */
class ReleaseResource extends Resource
{
    protected static ?string $model = Release::class;

    protected static bool $isScopedToTenant = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMusicalNote;

    protected static string|UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'title';

    public static function getModelLabel(): string
    {
        return __('sortie');
    }

    public static function getPluralModelLabel(): string
    {
        return __('sorties');
    }

    public static function form(Schema $schema): Schema
    {
        return ReleaseForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ReleaseInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReleasesTable::configure($table);
    }

    /**
     * @return array<string, mixed>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'upc', 'catalog_number'];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReleases::route('/'),
            'create' => CreateRelease::route('/create'),
            'view' => ViewRelease::route('/{record}'),
            'edit' => EditRelease::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->with('artist')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
