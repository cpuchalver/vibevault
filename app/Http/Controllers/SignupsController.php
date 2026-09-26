<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Billing\LabelSubscriptionService;
use App\Enums\BillingMode;
use App\Models\Label;
use Filament\Facades\Filament;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Post-signup screens: Checkout confirmation and payment resumption
 * (after a cancelled or failed Checkout). Owner-only.
 */
class SignupsController
{
    public function __construct(protected LabelSubscriptionService $subscriptions) {}

    public function completed(Label $label): View
    {
        Gate::authorize('manageBilling', $label);

        return view('signup.completed', [
            'label' => $label,
            'panelUrl' => Filament::getPanel('label')->getUrl($label),
        ]);
    }

    public function payment(Request $request, Label $label): View|RedirectResponse
    {
        Gate::authorize('manageBilling', $label);

        if ($label->hasActiveSubscription()) {
            return redirect()->away(Filament::getPanel('label')->getUrl($label));
        }

        return view('signup.payment', [
            'label' => $label,
            'hasFailed' => $request->boolean('erreur'),
        ]);
    }

    public function checkout(Label $label): RedirectResponse
    {
        Gate::authorize('manageBilling', $label);

        abort_unless($label->billing_mode === BillingMode::Stripe, Response::HTTP_NOT_FOUND);

        if ($label->hasActiveSubscription()) {
            return redirect()->away(Filament::getPanel('label')->getUrl($label));
        }

        try {
            $checkout = $this->subscriptions->checkout(
                $label,
                successUrl: route('signup.completed', ['label' => $label->slug]),
                cancelUrl: route('signup.payment', ['label' => $label->slug]),
            );
        } catch (ApiErrorException $exception) {
            Log::error('Stripe Checkout creation failed.', [
                'label_id' => $label->getKey(),
                'stripe_error' => $exception->getMessage(),
            ]);

            return redirect()->route('signup.payment', ['label' => $label->slug, 'erreur' => 1]);
        }

        return redirect()->away($checkout->url);
    }
}
