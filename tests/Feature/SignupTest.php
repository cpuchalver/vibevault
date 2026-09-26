<?php

declare(strict_types=1);

use App\Enums\BillingMode;
use App\Enums\BillingPeriod;
use App\Enums\LabelRole;
use App\Livewire\SignupForm;
use App\Models\DemoRequest;
use App\Models\Label;
use App\Models\User;
use Filament\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\Support\FakeStripeHttpClient;

use function Pest\Laravel\get;

beforeEach(function (): void {
    configureSelfServePlans();
    config()->set('marketing.signup.max_attempts_per_hour', 5);
    config()->set('marketing.demo_requests.minimum_fill_seconds', 3);
    config()->set('cashier.secret', 'sk_test_fake');

    RateLimiter::clear('signup:'.DemoRequest::hashIp('127.0.0.1'));
    Notification::fake();

    $this->stripe = FakeStripeHttpClient::install();
});

afterEach(function (): void {
    FakeStripeHttpClient::uninstall();
});

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function validSignup(array $overrides = []): array
{
    return [
        'plan' => 'label',
        'billing_period' => BillingPeriod::Yearly->value,
        'label_name' => 'Marée Records',
        'name' => 'Camille Durand',
        'email' => 'Camille@Maree-Records.fr',
        'password' => 'Un-mot-de-passe-solide-42',
        'password_confirmation' => 'Un-mot-de-passe-solide-42',
        'terms' => true,
        ...$overrides,
    ];
}

function submitSignup(array $overrides = []): Testable
{
    $component = Livewire::test(SignupForm::class);

    test()->travel(5)->seconds();

    return $component->fillForm(validSignup($overrides))->call('submit');
}

it('shows the signup page to guests with the plan chosen on the pricing page', function (): void {
    Livewire::withQueryParams(['formule' => 'independant', 'periode' => 'yearly'])
        ->test(SignupForm::class)
        ->assertSchemaStateSet([
            'plan' => 'independant',
            'billing_period' => BillingPeriod::Yearly,
        ]);

    get(route('signup'))->assertOk()->assertSee('14 jours pour tout essayer.');
});

it('ignores a plan that cannot be bought online', function (): void {
    Livewire::withQueryParams(['formule' => 'groupe'])
        ->test(SignupForm::class)
        ->assertSchemaStateSet(['plan' => null]);
});

it('redirects signed-in users away from the signup page', function (): void {
    $this->actingAs(User::factory()->create())
        ->get(route('signup'))
        ->assertRedirect('/label');
});

it('creates the owner, the self-serve label and sends them to Stripe Checkout', function (): void {
    submitSignup()
        ->assertHasNoFormErrors()
        ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_123');

    $owner = User::sole();
    $label = Label::sole();

    expect($owner)
        ->email->toBe('camille@maree-records.fr')
        ->hasVerifiedEmail()->toBeFalse()
        ->and(password_verify('Un-mot-de-passe-solide-42', $owner->password))->toBeTrue()
        ->and($label)
        ->name->toBe('Marée Records')
        ->billing_mode->toBe(BillingMode::Stripe)
        ->plan->toBe('label')
        ->billing_period->toBe(BillingPeriod::Yearly)
        ->stripe_id->toBe('cus_test_123')
        ->and($owner->roleInLabel($label))->toBe(LabelRole::Owner)
        ->and(auth()->id())->toBe($owner->getKey());

    Notification::assertSentTo($owner, VerifyEmail::class);
});

it('starts Checkout with the configured price, a trial, automatic tax and VAT number collection', function (): void {
    submitSignup();

    $session = $this->stripe->lastRequestTo('/v1/checkout/sessions')['params'];

    expect($session)
        ->mode->toBe('subscription')
        ->line_items->toBe([['price' => 'price_label_year', 'quantity' => 1]])
        ->automatic_tax->toBe(['enabled' => 'true'])
        ->tax_id_collection->toBe(['enabled' => 'true'])
        ->customer->toBe('cus_test_123')
        ->payment_method_collection->toBe('always')
        ->billing_address_collection->toBe('required')
        ->and($session['customer_update'])->toBe(['address' => 'auto', 'name' => 'auto'])
        ->and($session['subscription_data']['trial_end'])->toBeGreaterThan(now()->addDays(13)->getTimestamp())
        ->and($session['success_url'])->toBe(route('signup.completed', ['label' => Label::sole()->slug]))
        ->and($session['cancel_url'])->toBe(route('signup.payment', ['label' => Label::sole()->slug]));
});

it('never lets the visitor choose the charged price', function (): void {
    submitSignup(['plan' => 'groupe'])->assertHasFormErrors(['plan']);
    submitSignup(['plan' => 'price_label_year'])->assertHasFormErrors(['plan']);

    expect(Label::count())->toBe(0);
    expect($this->stripe->requests)->toBeEmpty();
});

it('validates the account fields', function (array $overrides, array $errors): void {
    submitSignup($overrides)->assertHasFormErrors($errors);

    expect(User::count())->toBe(0);
})->with([
    'missing label name' => [['label_name' => ''], ['label_name' => 'required']],
    'invalid email' => [['email' => 'nope'], ['email' => 'email']],
    'weak password' => [['password' => 'short', 'password_confirmation' => 'short'], ['password']],
    'mismatching confirmation' => [['password_confirmation' => 'Autre-mot-de-passe-42'], ['password' => 'same']],
    'terms not accepted' => [['terms' => false], ['terms' => 'accepted']],
]);

it('refuses an email that already has an account', function (): void {
    User::factory()->create(['email' => 'camille@maree-records.fr']);

    submitSignup(['email' => 'camille@maree-records.fr'])->assertHasFormErrors(['email' => 'unique']);

    expect(Label::count())->toBe(0);
});

it('silently drops bot submissions', function (): void {
    Livewire::test(SignupForm::class)
        ->set('website', 'https://spam.example')
        ->fillForm(validSignup())
        ->call('submit')
        ->assertNoRedirect();

    expect(User::count())->toBe(0);
    expect($this->stripe->requests)->toBeEmpty();
});

it('rate limits signups per visitor', function (): void {
    config()->set('marketing.signup.max_attempts_per_hour', 1);

    submitSignup()->assertHasNoFormErrors();

    auth()->logout();

    submitSignup(['email' => 'autre@maree-records.fr', 'label_name' => 'Autre label'])
        ->assertHasErrors(['data.email']);

    expect(Label::count())->toBe(1);
});

it('keeps the account and offers to retry when Stripe is unavailable', function (): void {
    $this->stripe->failCheckout = true;

    submitSignup()->assertRedirect(route('signup.payment', ['label' => Label::sole()->slug, 'erreur' => 1]));

    expect(Label::sole()->billing_mode)->toBe(BillingMode::Stripe);
});
