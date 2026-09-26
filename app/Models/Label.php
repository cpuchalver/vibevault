<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\BillingMode;
use App\Enums\BillingPeriod;
use App\Enums\LabelRole;
use App\Models\Concerns\GeneratesSlug;
use App\Policies\LabelPolicy;
use Database\Factories\LabelFactory;
use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Cashier\Billable;

/**
 * A music label: the top-level tenant of the platform.
 *
 * The label (not the user) is the Stripe customer: the subscription belongs
 * to the tenant and survives changes of owner. Billing columns are never
 * mass assignable; they are set explicitly by the signup flow.
 *
 * @property BillingMode $billing_mode
 * @property string|null $plan
 * @property BillingPeriod|null $billing_period
 */
#[UsePolicy(LabelPolicy::class)]
#[Fillable(['name', 'legal_name', 'country', 'contact_email'])]
class Label extends Model implements HasName
{
    use Billable;
    use GeneratesSlug;

    /** @use HasFactory<LabelFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @return BelongsToMany<User, $this, LabelMembership>
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->using(LabelMembership::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Artist, $this>
     */
    public function artists(): HasMany
    {
        return $this->hasMany(Artist::class);
    }

    /**
     * @return HasMany<Band, $this>
     */
    public function bands(): HasMany
    {
        return $this->hasMany(Band::class);
    }

    /**
     * @return HasMany<Release, $this>
     */
    public function releases(): HasMany
    {
        return $this->hasMany(Release::class);
    }

    public const string SubscriptionType = 'default';

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'billing_mode' => 'invoice',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'billing_mode' => BillingMode::class,
            'billing_period' => BillingPeriod::class,
            'trial_ends_at' => 'datetime',
        ];
    }

    /**
     * Whether members may use the label panel.
     *
     * Invoiced labels are managed by the platform; self-serve labels need a
     * valid subscription (active, trialing, or cancelled but in grace period).
     */
    public function hasActiveSubscription(): bool
    {
        if ($this->billing_mode !== BillingMode::Stripe) {
            return true;
        }

        return $this->subscribed(self::SubscriptionType);
    }

    public function owner(): ?User
    {
        return $this->members()
            ->wherePivot('role', LabelRole::Owner)
            ->oldest('label_user.created_at')
            ->first();
    }

    public function stripeEmail(): ?string
    {
        return $this->contact_email ?? $this->owner()?->email;
    }

    /**
     * @return list<string>
     */
    public function stripePreferredLocales(): array
    {
        return ['fr'];
    }

    public function getFilamentName(): string
    {
        return $this->name;
    }
}
