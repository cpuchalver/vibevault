<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * How a label pays for the platform.
 *
 * - Stripe: self-serve subscription; panel access requires an active or trialing subscription.
 * - Invoice: provisioned by the platform (quote-based plans, CLI onboarding); billed outside Stripe.
 */
enum BillingMode: string
{
    case Stripe = 'stripe';
    case Invoice = 'invoice';
}
