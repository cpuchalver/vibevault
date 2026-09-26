<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Bands;

use App\Filament\Label\Resources\Bands\Pages\CreateBand;
use App\Filament\Label\Resources\Bands\Pages\EditBand;
use App\Filament\Label\Resources\Bands\Pages\ListBands;
use App\Filament\Label\Resources\Bands\Pages\ViewBand;
use App\Filament\Label\Resources\Bands\RelationManagers\MembersRelationManager;
use App\Filament\Label\Resources\Bands\Schemas\BandForm;
use App\Filament\Label\Resources\Bands\Schemas\BandInfolist;
use App\Filament\Label\Resources\Bands\Tables\BandsTable;
use App\Models\Band;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

/**
 * Groups of the current label. Scoped by Filament tenancy (`label` ownership
 * relationship) and authorized by BandPolicy.
 */
class BandResource extends Resource
{
    protected static ?string $model = Band::class;

    protected static bool $isScopedToTenant = true;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Catalogue';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('groupe');
    }

    public static function getPluralModelLabel(): string
    {
        return __('groupes');
    }

    public static function form(Schema $schema): Schema
    {
        return BandForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BandInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BandsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBands::route('/'),
            'create' => CreateBand::route('/create'),
            'view' => ViewBand::route('/{record}'),
            'edit' => EditBand::route('/{record}/edit'),
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
