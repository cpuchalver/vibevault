<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\BillingMode;
use App\Models\Label;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ExceptionInterface as StripeException;
use Symfony\Component\HttpFoundation\Response;

/**
 * "Facturation" entry of the label panel (Filament tenant billing route).
 *
 * - Subscription on record: opens the Stripe customer portal (invoices,
 *   payment method, plan change, cancellation).
 * - No subscription yet: sends the owner to finish payment.
 * - Invoiced labels are billed by the platform: nothing to manage here.
 */
class LabelBillingController
{
    public function __invoke(): RedirectResponse
    {
        /** @var Label $label */
        $label = Filament::getTenant();

        Gate::authorize('manageBilling', $label);

        abort_if(
            $label->billing_mode !== BillingMode::Stripe,
            Response::HTTP_NOT_FOUND,
            'La facturation de ce label est gérée directement par VibeVault.',
        );

        if (! $label->hasStripeId() || ! $label->subscriptions()->exists()) {
            return redirect()->route('signup.payment', ['label' => $label->slug]);
        }

        try {
            return $label->redirectToBillingPortal(Filament::getPanel('label')->getUrl($label));
        } catch (StripeException $exception) {
            Log::error('Stripe billing portal creation failed.', [
                'label_id' => $label->getKey(),
                'stripe_error' => $exception->getMessage(),
            ]);

            return redirect()->route('signup.payment', ['label' => $label->slug, 'erreur' => 1]);
        }
    }
}
