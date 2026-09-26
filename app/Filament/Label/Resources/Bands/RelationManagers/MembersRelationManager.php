<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Bands\RelationManagers;

use App\Enums\BandType;
use App\Models\Band;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

/**
 * Members of a band.
 *
 * Security: Filament only checks `isReadOnly()` for attach/detach actions, so every
 * write action is explicitly authorized against BandPolicy::manageMembers().
 * Attachable artists are restricted to the band's own label (and re-checked on submit
 * by Filament, then by the BandMembership integrity guard).
 */
class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Membres');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(self::membershipFields());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modelLabel(__('membre'))
            ->pluralModelLabel(__('membres'))
            ->defaultSort('artist_band.joined_on')
            ->columns([
                TextColumn::make('name')
                    ->label(__('Artiste'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->label(__('Rôle'))
                    ->placeholder('—'),
                TextColumn::make('joined_on')
                    ->label(__('Arrivée'))
                    ->date()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('left_on')
                    ->label(__('Départ'))
                    ->date()
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('current')
                    ->label(__('Membres actuels'))
                    ->queries(
                        true: fn (Builder $query): Builder => $query->where(fn (Builder $query): Builder => $query
                            ->whereNull('artist_band.left_on')
                            ->orWhere('artist_band.left_on', '>', now())),
                        false: fn (Builder $query): Builder => $query->where('artist_band.left_on', '<=', now()),
                    ),
            ])
            ->headerActions([
                AttachAction::make()
                    ->authorize(fn (): bool => $this->canManageMembers())
                    // A solo project has exactly one member.
                    ->visible(fn (): bool => ! ($this->isSoloBand() && $this->membersCount() >= 1))
                    ->recordSelectOptionsQuery(fn (Builder $query): Builder => $query
                        ->where('artists.label_id', $this->getOwnerRecord()->label_id))
                    ->recordSelectSearchColumns(['name'])
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect()->label(__('Artiste')),
                        ...self::membershipFields(),
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->authorize(fn (): bool => $this->canManageMembers()),
                DetachAction::make()
                    ->authorize(fn (): bool => $this->canManageMembers())
                    // A band always keeps at least one member.
                    ->visible(fn (): bool => $this->membersCount() > 1),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->authorize(fn (): bool => $this->canManageMembers())
                        ->before(function (DetachBulkAction $action, Collection $records): void {
                            if ($records->count() < $this->membersCount()) {
                                return;
                            }

                            Notification::make()
                                ->danger()
                                ->title(__('Un groupe doit garder au moins un membre.'))
                                ->send();

                            $action->halt();
                        }),
                ]),
            ]);
    }

    /**
     * Memoized for the request: evaluated once, not once per table row.
     */
    protected function membersCount(): int
    {
        return once(fn (): int => $this->getOwnerRecord()->members()->count());
    }

    protected function isSoloBand(): bool
    {
        /** @var Band $band */
        $band = $this->getOwnerRecord();

        return $band->type === BandType::Solo;
    }

    protected function canManageMembers(): bool
    {
        /** @var Band $band */
        $band = $this->getOwnerRecord();

        return ! $this->isReadOnly() && Gate::allows('manageMembers', $band);
    }

    /**
     * @return array<int, TextInput|DatePicker>
     */
    protected static function membershipFields(): array
    {
        return [
            TextInput::make('role')
                ->label(__('Rôle / instrument'))
                ->maxLength(120),
            DatePicker::make('joined_on')
                ->label(__('Arrivée')),
            DatePicker::make('left_on')
                ->label(__('Départ'))
                ->afterOrEqual(fn (Get $get): ?string => $get('joined_on') ?: null),
        ];
    }
}
