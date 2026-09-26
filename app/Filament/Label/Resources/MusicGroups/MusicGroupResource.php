<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\MusicGroups;

use App\Filament\Label\Resources\MusicGroups\Pages\CreateMusicGroup;
use App\Filament\Label\Resources\MusicGroups\Pages\EditMusicGroup;
use App\Filament\Label\Resources\MusicGroups\Pages\ListMusicGroups;
use App\Filament\Label\Resources\MusicGroups\Pages\ViewMusicGroup;
use App\Filament\Label\Resources\MusicGroups\RelationManagers\MembersRelationManager;
use App\Filament\Label\Resources\MusicGroups\Schemas\MusicGroupForm;
use App\Filament\Label\Resources\MusicGroups\Schemas\MusicGroupInfolist;
use App\Filament\Label\Resources\MusicGroups\Tables\MusicGroupsTable;
use App\Models\MusicGroup;
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
 * relationship) and authorized by MusicGroupPolicy.
 */
class MusicGroupResource extends Resource
{
    protected static ?string $model = MusicGroup::class;

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
        return MusicGroupForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MusicGroupInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MusicGroupsTable::configure($table);
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
            'index' => ListMusicGroups::route('/'),
            'create' => CreateMusicGroup::route('/create'),
            'view' => ViewMusicGroup::route('/{record}'),
            'edit' => EditMusicGroup::route('/{record}/edit'),
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
