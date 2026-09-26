<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Label;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks the label panel while a self-serve label has no valid subscription.
 * Owners are sent to billing to fix it; other members are told to ask them.
 */
class EnsureLabelIsSubscribed
{
    public function handle(Request $request, Closure $next): Response
    {
        $label = Filament::getTenant();

        if (! $label instanceof Label || $label->hasActiveSubscription()) {
            return $next($request);
        }

        abort_unless(
            $request->user()?->can('manageBilling', $label),
            Response::HTTP_PAYMENT_REQUIRED,
            'L’abonnement de ce label n’est pas actif. Contactez le propriétaire du label.',
        );

        return redirect(Filament::getTenantBillingUrl(tenant: $label));
    }
}
