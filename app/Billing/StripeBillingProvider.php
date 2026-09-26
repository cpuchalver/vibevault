<?php

declare(strict_types=1);

namespace App\Billing;

use App\Http\Controllers\LabelBillingController;
use App\Http\Middleware\EnsureLabelIsSubscribed;
use Filament\Billing\Providers\Contracts\BillingProvider;

/**
 * Filament tenant billing for the label panel: adds the "Facturation" entry
 * to the tenant menu and requires a valid subscription on every page.
 */
class StripeBillingProvider implements BillingProvider
{
    public function getRouteAction(): string
    {
        return LabelBillingController::class;
    }

    public function getSubscribedMiddleware(): string
    {
        return EnsureLabelIsSubscribed::class;
    }
}
