<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Http\Middleware\VerifyWebhookSignature;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rejects every Stripe webhook whose signature cannot be verified.
 *
 * Cashier skips signature verification when no secret is configured, which
 * would let anyone forge "subscription active" events. Here a missing secret
 * means every webhook is refused.
 */
class VerifyStripeWebhookSignature
{
    public function __construct(protected VerifyWebhookSignature $cashierVerification) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (blank(config('cashier.webhook.secret'))) {
            Log::critical('Stripe webhook rejected: STRIPE_WEBHOOK_SECRET is not configured.');

            abort(Response::HTTP_FORBIDDEN);
        }

        return $this->cashierVerification->handle($request, $next);
    }
}
