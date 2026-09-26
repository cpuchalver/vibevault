<?php

declare(strict_types=1);

namespace App\Filament\Label\Resources\Bands\Schemas;

use App\Enums\BandType;
use App\Models\Artist;
use App\Models\Band;
use Closure;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class BandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('Groupe'))
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label(__('Nom'))
                            ->required()
                            ->maxLength(120),
                        Select::make('type')
                            ->label(__('Type de formation'))
                            ->options(BandType::class)
                            ->default(BandType::Band)
                            ->live()
                            ->required()
                            ->rules([
                                fn (?Band $record): Closure => function (string $attribute, mixed $value, Closure $fail) use ($record): void {
                                    if (self::isSolo($value) && $record?->members()->count() > 1) {
                                        $fail(__('Un groupe de plusieurs membres ne peut pas être un projet solo.'));
                                    }
                                },
                            ]),
                        // Composition at creation time. Afterwards, members (with role and
                        // tenure) are managed in the "Membres" relation manager.
                        Select::make('members')
                            ->label(__('Membres'))
                            ->helperText(__('Au moins un artiste ; un seul pour un projet solo.'))
                            ->relationship(
                                name: 'members',
                                titleAttribute: 'name',
                                modifyQueryUsing: fn (Builder $query): Builder => $query->whereBelongsTo(Filament::getTenant()),
                            )
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->minItems(1)
                            ->maxItems(fn (Get $get): ?int => self::isSolo($get('type')) ? 1 : null)
                            // Defense in depth: every submitted id must be an artist of the current label.
                            ->rules([
                                fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                                    $ids = array_filter((array) $value);

                                    $validCount = Artist::query()
                                        ->whereBelongsTo(Filament::getTenant())
                                        ->whereKey($ids)
                                        ->count();

                                    if ($validCount !== count($ids)) {
                                        $fail(__('Sélection d\'artistes invalide.'));
                                    }
                                },
                            ])
                            ->visibleOn('create')
                            ->columnSpanFull(),
                        TextInput::make('country')
                            ->label(__('Pays (ISO 3166-1 alpha-2)'))
                            ->length(2)
                            ->regex('/^[A-Z]{2}$/')
                            ->dehydrateStateUsing(fn (?string $state): ?string => $state ? strtoupper($state) : null),
                        TextInput::make('isni')
                            ->label(__('ISNI'))
                            ->regex('/^\d{15}[\dX]$/')
                            ->helperText(__('16 caractères, sans espaces.')),
                        DatePicker::make('formed_on')
                            ->label(__('Date de formation')),
                        DatePicker::make('disbanded_on')
                            ->label(__('Date de séparation'))
                            ->afterOrEqual(fn (Get $get): ?string => $get('formed_on') ?: null),
                        Textarea::make('biography')
                            ->label(__('Biographie'))
                            ->rows(5)
                            ->maxLength(5000)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    private static function isSolo(mixed $type): bool
    {
        return ($type instanceof BandType ? $type : BandType::tryFrom((string) $type)) === BandType::Solo;
    }
}
