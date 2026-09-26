<?php

declare(strict_types=1);

namespace App\Billing;

use App\Enums\BillingMode;
use App\Enums\BillingPeriod;
use App\Enums\LabelRole;
use App\Models\Label;
use App\Models\User;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Laravel\Cashier\Checkout;

/**
 * Self-serve onboarding: account + label creation, then Stripe Checkout.
 *
 * The plan and period only ever select a price ID from {@see PlanCatalog};
 * the amount charged is defined in Stripe, never by the request.
 */
class LabelSubscriptionService
{
    public function __construct(protected PlanCatalog $plans) {}

    /**
     * Creates the owner account (unverified) and its label in one transaction.
     */
    public function register(
        string $name,
        string $email,
        string $password,
        string $labelName,
        string $plan,
        BillingPeriod $period,
    ): Label {
        $this->ensureSelfServe($plan);

        $label = DB::transaction(function () use ($name, $email, $password, $labelName, $plan, $period): Label {
            $owner = User::query()->create([
                'name' => $name,
                'email' => Str::lower($email),
                'password' => $password,
            ]);

            $label = Label::query()->create([
                'name' => $labelName,
                'contact_email' => $owner->email,
            ]);

            $label->forceFill([
                'billing_mode' => BillingMode::Stripe,
                'plan' => $plan,
                'billing_period' => $period,
            ])->save();

            $label->members()->attach($owner, ['role' => LabelRole::Owner]);

            return $label;
        });

        $this->sendEmailVerification($label->owner());

        return $label;
    }

    /**
     * Starts a Stripe Checkout session for the label's chosen plan, with the
     * trial, automatic tax and B2B VAT number collection.
     */
    public function checkout(Label $label, string $successUrl, string $cancelUrl): Checkout
    {
        $this->ensureSelfServe($label->plan);

        $priceId = $this->plans->priceId($label->plan, $label->billing_period ?? BillingPeriod::Monthly);

        $builder = $label->newSubscription(Label::SubscriptionType, $priceId)
            ->allowPromotionCodes();

        if (! $this->hasAlreadyTrialed($label)) {
            $builder->trialDays($this->plans->trialDays());
        }

        return $builder->checkout([
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $label->getKey(),
            'locale' => 'fr',
            'payment_method_collection' => 'always',
            'tax_id_collection' => ['enabled' => true],
            'customer_update' => ['address' => 'auto', 'name' => 'auto'],
            'subscription_data' => [
                'metadata' => [
                    'label_id' => (string) $label->getKey(),
                    'plan' => $label->plan,
                ],
            ],
        ], [
            'name' => $label->legal_name ?? $label->name,
            'metadata' => ['label_id' => (string) $label->getKey()],
        ]);
    }

    /**
     * A label gets a single free trial: resuming or re-subscribing is paid.
     */
    protected function hasAlreadyTrialed(Label $label): bool
    {
        return $label->subscriptions()->exists();
    }

    protected function ensureSelfServe(?string $plan): void
    {
        if (! $this->plans->isSelfServe($plan)) {
            throw new InvalidArgumentException("Plan [{$plan}] is not available for online subscription.");
        }
    }

    protected function sendEmailVerification(?User $user): void
    {
        if ($user === null || $user->hasVerifiedEmail()) {
            return;
        }

        $notification = app(VerifyEmail::class);
        $notification->url = Filament::getPanel(User::LabelPanel)->getVerifyEmailUrl($user);

        $user->notify($notification);
    }
}
