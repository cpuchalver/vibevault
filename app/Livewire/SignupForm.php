<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Billing\LabelSubscriptionService;
use App\Billing\PlanCatalog;
use App\Enums\BillingPeriod;
use App\Filament\Label\Tenancy\SubscriptionPlanFields;
use App\Models\DemoRequest;
use App\Models\User;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Public signup: creates the owner account and the label, signs the owner
 * in and sends them to the label dashboard. No Stripe call happens here: the
 * panel first asks for email verification, then its subscription requirement
 * leads the owner to Stripe Checkout (trial, payment method).
 *
 * Anonymous endpoint: rate limited per IP (hashed), honeypot, minimum fill
 * time. No card data ever reaches the application.
 */
class SignupForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    #[Url(as: 'formule')]
    public ?string $initialPlan = null;

    #[Url(as: 'periode')]
    public ?string $initialPeriod = null;

    public string $website = '';

    #[Locked]
    public int $renderedAt = 0;

    public function mount(PlanCatalog $plans): void
    {
        $this->renderedAt = now()->getTimestamp();

        $this->form->fill([
            'plan' => $plans->isSelfServe($this->initialPlan) ? $this->initialPlan : null,
            'billing_period' => (BillingPeriod::tryFrom((string) $this->initialPeriod) ?? BillingPeriod::Monthly)->value,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Votre formule')
                    ->columns(['default' => 1, 'sm' => 2])
                    ->schema(SubscriptionPlanFields::components()),
                Section::make('Votre label')
                    ->schema([
                        TextInput::make('label_name')
                            ->label('Nom du label')
                            ->autocomplete('organization')
                            ->required()
                            ->maxLength(120),
                    ]),
                Section::make('Votre compte')
                    ->description('Vous serez le propriétaire du label et pourrez inviter votre équipe.')
                    ->schema([
                        Grid::make(['default' => 1, 'sm' => 2])
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nom complet')
                                    ->autocomplete('name')
                                    ->required()
                                    ->maxLength(120),
                                TextInput::make('email')
                                    ->label('Email professionnel')
                                    ->email()
                                    ->autocomplete('email')
                                    ->required()
                                    ->maxLength(190)
                                    ->unique(User::class, 'email')
                                    ->validationMessages([
                                        'unique' => 'Un compte existe déjà avec cet email. Connectez-vous pour créer un nouveau label.',
                                    ]),
                                TextInput::make('password')
                                    ->label('Mot de passe')
                                    ->password()
                                    ->revealable()
                                    ->autocomplete('new-password')
                                    ->required()
                                    ->rule(Password::defaults())
                                    ->same('password_confirmation'),
                                TextInput::make('password_confirmation')
                                    ->label('Confirmation du mot de passe')
                                    ->password()
                                    ->revealable()
                                    ->autocomplete('new-password')
                                    ->required()
                                    ->dehydrated(false),
                            ]),
                    ]),
                Checkbox::make('terms')
                    ->label(new HtmlString(
                        'J’accepte les <a href="'.e(route('legal.terms')).'" class="underline" target="_blank">conditions générales</a> '
                        .'et la <a href="'.e(route('legal.privacy')).'" class="underline" target="_blank">politique de confidentialité</a>.'
                    ))
                    ->accepted()
                    ->validationMessages([
                        'accepted' => 'Vous devez accepter les conditions pour créer votre compte.',
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(LabelSubscriptionService $subscriptions): void
    {
        $this->ensureIsNotRateLimited();

        $state = $this->form->getState();

        RateLimiter::hit($this->throttleKey(), 3600);

        if ($this->isLikelyBot()) {
            return;
        }

        $label = $subscriptions->register(
            name: $state['name'],
            email: $state['email'],
            password: $state['password'],
            labelName: $state['label_name'],
            plan: $state['plan'],
            period: $state['billing_period'] instanceof BillingPeriod ? $state['billing_period'] : BillingPeriod::from($state['billing_period']),
        );

        Auth::login($label->owner());
        session()->regenerate();

        $this->redirect(Filament::getPanel(User::LabelPanel)->getUrl($label));
    }

    public function render(): View
    {
        return view('livewire.signup-form');
    }

    protected function isLikelyBot(): bool
    {
        $minimumSeconds = config('marketing.demo_requests.minimum_fill_seconds');
        $isTooFast = (now()->getTimestamp() - $this->renderedAt) < $minimumSeconds;

        return $this->website !== '' || $isTooFast;
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), config('marketing.signup.max_attempts_per_hour'))) {
            return;
        }

        $minutes = (int) ceil(RateLimiter::availableIn($this->throttleKey()) / 60);

        throw ValidationException::withMessages([
            'data.email' => "Trop de tentatives d’inscription depuis votre connexion. Réessayez dans {$minutes} min.",
        ]);
    }

    protected function throttleKey(): string
    {
        return 'signup:'.DemoRequest::hashIp(request()->ip());
    }
}
