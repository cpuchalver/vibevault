<?php

declare(strict_types=1);

namespace App\Billing;

use App\Enums\BillingPeriod;

/**
 * Read-only access to the plans defined in `config/marketing.php`.
 *
 * Only plans with both Stripe prices configured are sold online; any price
 * ID used to start a subscription comes from here, never from user input.
 */
class PlanCatalog
{
    /**
     * @return array<string, string> plan key => plan name
     */
    public function selfServeOptions(): array
    {
        return collect($this->plans())
            ->filter(fn (array $plan): bool => $this->isSelfServe($plan['key']))
            ->mapWithKeys(fn (array $plan): array => [$plan['key'] => $plan['name']])
            ->all();
    }

    public function isSelfServe(?string $planKey): bool
    {
        $plan = $this->find($planKey);

        if ($plan === null || $plan['monthly_price'] === null) {
            return false;
        }

        return filled($plan['stripe_prices']['monthly'] ?? null)
            && filled($plan['stripe_prices']['yearly'] ?? null);
    }

    public function priceId(string $planKey, BillingPeriod $period): ?string
    {
        if (! $this->isSelfServe($planKey)) {
            return null;
        }

        return $this->find($planKey)['stripe_prices'][$period->value];
    }

    public function name(?string $planKey): ?string
    {
        return $this->find($planKey)['name'] ?? null;
    }

    /**
     * @return array{key: string, name: string, monthly_price: int|null, stripe_prices?: array<string, string|null>}|null
     */
    public function find(?string $planKey): ?array
    {
        if ($planKey === null) {
            return null;
        }

        return collect($this->plans())->firstWhere('key', $planKey);
    }

    public function trialDays(): int
    {
        return config('marketing.trial_days');
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function plans(): array
    {
        return config('marketing.plans');
    }
}
