<?php

declare(strict_types=1);

use App\Enums\BillingMode;
use App\Enums\BillingPeriod;
use App\Enums\LabelRole;
use App\Models\Label;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Makes the "independant" and "label" plans sellable online with test prices.
 */
function configureSelfServePlans(): void
{
    config()->set('marketing.trial_days', 14);
    config()->set('marketing.plans', [
        [
            'key' => 'independant', 'name' => 'Indépendant', 'audience' => 'Test', 'monthly_price' => 49, 'highlighted' => false, 'features' => [],
            'stripe_prices' => ['monthly' => 'price_ind_month', 'yearly' => 'price_ind_year'],
        ],
        [
            'key' => 'label', 'name' => 'Label', 'audience' => 'Test', 'monthly_price' => 149, 'highlighted' => true, 'features' => [],
            'stripe_prices' => ['monthly' => 'price_label_month', 'yearly' => 'price_label_year'],
        ],
        [
            'key' => 'groupe', 'name' => 'Groupe', 'audience' => 'Test', 'monthly_price' => null, 'highlighted' => false, 'features' => [],
        ],
    ]);
}

/**
 * A self-serve label owned by the given user, without any subscription.
 */
function createStripeLabel(User $owner, LabelRole $role = LabelRole::Owner): Label
{
    $label = Label::factory()->withMember($owner, $role)->create();

    $label->forceFill([
        'billing_mode' => BillingMode::Stripe,
        'plan' => 'label',
        'billing_period' => BillingPeriod::Monthly,
    ])->save();

    return $label;
}

/**
 * Records a Stripe subscription for the label, as a webhook would.
 */
function subscribeLabel(Label $label, string $status = 'active', ?CarbonInterface $trialEndsAt = null, ?CarbonInterface $endsAt = null): void
{
    $label->forceFill(['stripe_id' => 'cus_'.Str::random(10)])->save();

    $label->subscriptions()->create([
        'type' => Label::SubscriptionType,
        'stripe_id' => 'sub_'.Str::random(10),
        'stripe_status' => $status,
        'stripe_price' => 'price_label_month',
        'quantity' => 1,
        'trial_ends_at' => $trialEndsAt,
        'ends_at' => $endsAt,
    ]);
}
