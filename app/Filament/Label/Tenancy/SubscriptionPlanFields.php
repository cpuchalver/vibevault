<?php

declare(strict_types=1);

namespace App\Filament\Label\Tenancy;

use App\Billing\PlanCatalog;
use App\Enums\BillingPeriod;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Illuminate\Validation\Rule;

/**
 * Plan and billing period fields, shared by the public signup form and the
 * in-panel label registration. Only self-serve plans can be selected.
 */
class SubscriptionPlanFields
{
    /**
     * @return array<int, Select|ToggleButtons>
     */
    public static function components(): array
    {
        $plans = app(PlanCatalog::class)->selfServeOptions();

        return [
            Select::make('plan')
                ->label('Formule')
                ->options($plans)
                ->native()
                ->required()
                ->rule(Rule::in(array_keys($plans))),
            ToggleButtons::make('billing_period')
                ->label('Facturation')
                ->options(BillingPeriod::class)
                ->default(BillingPeriod::Monthly->value)
                ->inline()
                ->required(),
        ];
    }
}
