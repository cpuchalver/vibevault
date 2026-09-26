<?php

declare(strict_types=1);

namespace App\Filament\Label\Tenancy;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

/**
 * Label profile fields shared by the registration and profile pages.
 */
class LabelForm
{
    /**
     * @return array<int, Section>
     */
    public static function components(): array
    {
        return [
            Section::make(__('Identité du label'))
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->label(__('Nom commercial'))
                        ->required()
                        ->maxLength(120),
                    TextInput::make('legal_name')
                        ->label(__('Raison sociale'))
                        ->maxLength(160),
                    TextInput::make('country')
                        ->label(__('Pays (ISO 3166-1 alpha-2)'))
                        ->length(2)
                        ->regex('/^[A-Z]{2}$/')
                        ->dehydrateStateUsing(fn (?string $state): ?string => $state ? strtoupper($state) : null),
                    TextInput::make('contact_email')
                        ->label(__('E-mail de contact'))
                        ->email()
                        ->maxLength(190),
                ]),
        ];
    }
}
