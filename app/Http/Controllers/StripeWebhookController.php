<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Middleware\VerifyStripeWebhookSignature;
use Laravel\Cashier\Http\Controllers\WebhookController;

/**
 * Cashier's webhook handling, with mandatory signature verification.
 */
class StripeWebhookController extends WebhookController
{
    public function __construct()
    {
        $this->middleware(VerifyStripeWebhookSignature::class);
    }
}
