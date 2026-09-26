<?php

declare(strict_types=1);

namespace App\Filament\Label\Tenancy;

use App\Enums\BillingMode;
use App\Enums\BillingPeriod;
use App\Enums\LabelRole;
use App\Models\Label;
use App\Policies\LabelPolicy;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

/**
 * Access is authorized by {@see LabelPolicy::create()} through
 * the parent `canView()`, checked on mount, hydrate and submit.
 *
 * Every label created here is a self-serve Stripe label: once registered, the
 * panel's subscription requirement sends the owner to payment before any use.
 */
class RegisterLabel extends RegisterTenant
{
    public static function getLabel(): string
    {
        return __('Créer un label');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            ...LabelForm::components(),
            Section::make(__('Abonnement'))
                ->columns(2)
                ->schema(SubscriptionPlanFields::components()),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRegistration(array $data): Label
    {
        $label = Label::create(Arr::except($data, ['plan', 'billing_period']));

        $label->forceFill([
            'billing_mode' => BillingMode::Stripe,
            'plan' => $data['plan'],
            'billing_period' => $data['billing_period'] instanceof BillingPeriod ? $data['billing_period'] : BillingPeriod::from($data['billing_period']),
        ])->save();

        $label->members()->attach(Auth::user(), ['role' => LabelRole::Owner]);

        return $label;
    }
}
