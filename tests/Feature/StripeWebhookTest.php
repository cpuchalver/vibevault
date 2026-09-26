<?php

declare(strict_types=1);

use App\Models\Label;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

use function Pest\Laravel\call;

const WebhookSecret = 'whsec_test_secret';

beforeEach(function (): void {
    config()->set('cashier.webhook.secret', WebhookSecret);

    $this->label = createStripeLabel(User::factory()->create());
    $this->label->forceFill(['stripe_id' => 'cus_webhook'])->save();
});

/**
 * @param  array<string, mixed>  $payload
 */
function postStripeWebhook(array $payload, ?string $secret = WebhookSecret): TestResponse
{
    $body = json_encode($payload, JSON_THROW_ON_ERROR);
    $timestamp = time();
    $headers = ['CONTENT_TYPE' => 'application/json'];

    if ($secret !== null) {
        $signature = hash_hmac('sha256', "{$timestamp}.{$body}", $secret);
        $headers['HTTP_STRIPE_SIGNATURE'] = "t={$timestamp},v1={$signature}";
    }

    return call('POST', route('cashier.webhook'), [], [], [], $headers, $body);
}

/**
 * @return array<string, mixed>
 */
function subscriptionEvent(string $type, string $status, array $object = []): array
{
    return [
        'id' => 'evt_test',
        'type' => $type,
        'data' => [
            'object' => [
                'id' => 'sub_webhook',
                'object' => 'subscription',
                'customer' => 'cus_webhook',
                'status' => $status,
                'trial_end' => now()->addDays(14)->getTimestamp(),
                'cancel_at_period_end' => false,
                'metadata' => ['type' => Label::SubscriptionType],
                'items' => ['data' => [[
                    'id' => 'si_webhook',
                    'price' => ['id' => 'price_label_month', 'product' => 'prod_label'],
                    'quantity' => 1,
                ]]],
                ...$object,
            ],
        ],
    ];
}

it('records the subscription created by Checkout and opens the panel', function (): void {
    postStripeWebhook(subscriptionEvent('customer.subscription.created', 'trialing'))->assertOk();

    $label = $this->label->fresh();

    expect($label->subscription(Label::SubscriptionType))
        ->stripe_status->toBe('trialing')
        ->stripe_price->toBe('price_label_month')
        ->and($label->onTrial(Label::SubscriptionType))->toBeTrue()
        ->and($label->hasActiveSubscription())->toBeTrue();
});

it('closes access when Stripe deletes the subscription', function (): void {
    postStripeWebhook(subscriptionEvent('customer.subscription.created', 'active'))->assertOk();
    postStripeWebhook(subscriptionEvent('customer.subscription.deleted', 'canceled'))->assertOk();

    expect($this->label->fresh()->hasActiveSubscription())->toBeFalse();
});

it('rejects webhooks with an invalid signature', function (): void {
    postStripeWebhook(subscriptionEvent('customer.subscription.created', 'active'), secret: 'whsec_forged')
        ->assertForbidden();

    expect($this->label->subscriptions()->count())->toBe(0);
});

it('rejects unsigned webhooks', function (): void {
    postStripeWebhook(subscriptionEvent('customer.subscription.created', 'active'), secret: null)
        ->assertForbidden();

    expect($this->label->subscriptions()->count())->toBe(0);
});

it('rejects every webhook when no signing secret is configured', function (): void {
    config()->set('cashier.webhook.secret', null);

    postStripeWebhook(subscriptionEvent('customer.subscription.created', 'active'), secret: '')
        ->assertForbidden();

    expect($this->label->subscriptions()->count())->toBe(0);
});

it('ignores subscriptions of unknown customers', function (): void {
    postStripeWebhook(subscriptionEvent('customer.subscription.created', 'active', ['customer' => 'cus_someone_else']))
        ->assertOk();

    expect(DB::table('subscriptions')->count())->toBe(0);
});
