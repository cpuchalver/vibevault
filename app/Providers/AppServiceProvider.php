<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Label;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentColor;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        Cashier::ignoreRoutes();
    }

    public function boot(): void
    {
        FilamentColor::register([
            'primary' => [
                ...Color::Indigo,
                600 => Color::convertToOklch('#2b3ae6'),
                700 => Color::convertToOklch('#1f2bb3'),
            ],
        ]);

        $this->configureBilling();
    }

    /**
     * Labels (tenants) are the Stripe customers. Taxes are computed by Stripe
     * Tax (French VAT, EU reverse charge with a valid VAT number). Past-due
     * subscriptions keep access while Stripe retries the payment.
     */
    protected function configureBilling(): void
    {
        Cashier::useCustomerModel(Label::class);
        Cashier::calculateTaxes();
        Cashier::keepPastDueSubscriptionsActive();
    }
}
