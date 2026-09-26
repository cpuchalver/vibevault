<?php

declare(strict_types=1);

use App\Enums\BillingMode;
use App\Enums\BillingPeriod;
use App\Enums\LabelRole;
use App\Filament\Label\Tenancy\RegisterLabel;
use App\Models\Label;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Tests\Support\FakeStripeHttpClient;

beforeEach(function (): void {
    configureSelfServePlans();
    config()->set('cashier.secret', 'sk_test_fake');

    $this->stripe = FakeStripeHttpClient::install();
    $this->owner = User::factory()->create();
});

afterEach(function (): void {
    FakeStripeHttpClient::uninstall();
});

describe('panel access', function (): void {
    it('lets invoiced labels in without a Stripe subscription', function (): void {
        $label = Label::factory()->withMember($this->owner)->create();

        expect($label->billing_mode)->toBe(BillingMode::Invoice);

        $this->actingAs($this->owner)->get("/label/{$label->slug}")->assertOk();
    });

    it('sends the owner of an unpaid self-serve label to billing', function (): void {
        $label = createStripeLabel($this->owner);

        $this->actingAs($this->owner)
            ->get("/label/{$label->slug}/releases")
            ->assertRedirect("/label/{$label->slug}/facturation");
    });

    it('tells other members that the subscription is inactive', function (): void {
        $label = createStripeLabel($this->owner);
        $viewer = User::factory()->create();
        $label->members()->attach($viewer, ['role' => LabelRole::CatalogManager]);

        $this->actingAs($viewer)
            ->get("/label/{$label->slug}")
            ->assertStatus(402);
    });

    it('opens the panel during the trial, while active and during the cancellation grace period', function (string $status, ?string $trialEnds, ?string $ends): void {
        $label = createStripeLabel($this->owner);
        subscribeLabel($label, $status, $trialEnds ? now()->modify($trialEnds) : null, $ends ? now()->modify($ends) : null);

        $this->actingAs($this->owner)->get("/label/{$label->slug}")->assertOk();
    })->with([
        'trialing' => ['trialing', '+10 days', null],
        'active' => ['active', null, null],
        'past due while Stripe retries' => ['past_due', null, null],
        'cancelled, grace period' => ['active', null, '+5 days'],
    ]);

    it('closes the panel once the subscription has ended or is unpaid', function (string $status, ?string $ends): void {
        $label = createStripeLabel($this->owner);
        subscribeLabel($label, $status, null, $ends ? now()->modify($ends) : null);

        $this->actingAs($this->owner)
            ->get("/label/{$label->slug}")
            ->assertRedirect("/label/{$label->slug}/facturation");
    })->with([
        'cancelled and ended' => ['canceled', '-1 day'],
        'unpaid' => ['unpaid', null],
        'incomplete checkout' => ['incomplete', null],
    ]);
});

describe('billing page', function (): void {
    it('opens the Stripe customer portal for a subscribed label', function (): void {
        $label = createStripeLabel($this->owner);
        subscribeLabel($label);

        $this->actingAs($this->owner)
            ->get("/label/{$label->slug}/facturation")
            ->assertRedirect('https://billing.stripe.com/p/session/test_123');

        expect($this->stripe->lastRequestTo('/v1/billing_portal/sessions')['params']['return_url'])
            ->toBe(Filament::getPanel('label')->getUrl($label));
    });

    it('sends the owner to payment when no subscription exists yet', function (): void {
        $label = createStripeLabel($this->owner);

        $this->actingAs($this->owner)
            ->get("/label/{$label->slug}/facturation")
            ->assertRedirect(route('signup.payment', ['label' => $label->slug]));
    });

    it('is reserved to members who manage the label', function (LabelRole $role): void {
        $label = createStripeLabel($this->owner);
        subscribeLabel($label);
        $member = User::factory()->create();
        $label->members()->attach($member, ['role' => $role]);

        $this->actingAs($member)
            ->get("/label/{$label->slug}/facturation")
            ->assertForbidden();

        expect($this->stripe->requests)->toBeEmpty();
    })->with([LabelRole::Admin, LabelRole::Accountant, LabelRole::Viewer]);

    it('has nothing to manage for invoiced labels', function (): void {
        $label = Label::factory()->withMember($this->owner)->create();

        $this->actingAs($this->owner)
            ->get("/label/{$label->slug}/facturation")
            ->assertNotFound();
    });
});

describe('payment resumption', function (): void {
    it('starts a new Checkout without a second free trial', function (): void {
        $label = createStripeLabel($this->owner);
        subscribeLabel($label, 'canceled', null, now()->subDay());

        $this->actingAs($this->owner)
            ->post(route('signup.checkout', ['label' => $label->slug]))
            ->assertRedirect('https://checkout.stripe.com/c/pay/cs_test_123');

        $session = $this->stripe->lastRequestTo('/v1/checkout/sessions')['params'];

        expect($session['subscription_data'])->not->toHaveKey('trial_end')
            ->and($session['line_items'])->toBe([['price' => 'price_label_month', 'quantity' => 1]]);
    });

    it('refuses to act on another label', function (): void {
        $label = createStripeLabel($this->owner);
        $stranger = User::factory()->create();
        createStripeLabel($stranger);

        $this->actingAs($stranger)->get(route('signup.payment', ['label' => $label->slug]))->assertForbidden();
        $this->actingAs($stranger)->post(route('signup.checkout', ['label' => $label->slug]))->assertForbidden();
        $this->actingAs($stranger)->get(route('signup.completed', ['label' => $label->slug]))->assertForbidden();

        expect($this->stripe->requests)->toBeEmpty();
    });

    it('requires authentication', function (): void {
        $label = createStripeLabel($this->owner);

        $this->post(route('signup.checkout', ['label' => $label->slug]))
            ->assertRedirect(route('filament.label.auth.login'));
    });

    it('does not open Checkout for invoiced labels', function (): void {
        $label = Label::factory()->withMember($this->owner)->create();

        $this->actingAs($this->owner)
            ->post(route('signup.checkout', ['label' => $label->slug]))
            ->assertNotFound();
    });
});

describe('additional labels created from the panel', function (): void {
    it('are self-serve labels that must be paid for', function (): void {
        $existing = Label::factory()->withMember($this->owner)->create();

        $this->actingAs($this->owner);
        Filament::setCurrentPanel('label');
        Filament::setTenant($existing);

        Livewire::test(RegisterLabel::class)
            ->fillForm([
                'name' => 'Second Label',
                'plan' => 'independant',
                'billing_period' => BillingPeriod::Yearly->value,
            ])
            ->call('register')
            ->assertHasNoFormErrors();

        $label = Label::query()->where('name', 'Second Label')->sole();

        expect($label)
            ->billing_mode->toBe(BillingMode::Stripe)
            ->plan->toBe('independant')
            ->billing_period->toBe(BillingPeriod::Yearly)
            ->and($label->hasActiveSubscription())->toBeFalse()
            ->and($this->owner->roleInLabel($label))->toBe(LabelRole::Owner);
    });

    it('cannot be created when no plan is sold online', function (): void {
        config()->set('marketing.plans', []);
        Label::factory()->withMember($this->owner)->create();

        expect($this->owner->can('create', Label::class))->toBeFalse();
    });
});
