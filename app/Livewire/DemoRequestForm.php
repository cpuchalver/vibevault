<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Enums\CatalogueSize;
use App\Enums\DemoRequesterRole;
use App\Models\DemoRequest;
use App\Notifications\DemoRequestReceived;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\HtmlString;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * Public demo request form.
 *
 * Anonymous endpoint, so it is hardened on its own:
 * - per-IP rate limiting (hashed key, no raw IP stored),
 * - honeypot field and minimum fill time against bots,
 * - explicit GDPR consent, timestamped,
 * - strict length limits on every field.
 */
class DemoRequestForm extends Component implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Honeypot: invisible to humans, left empty by them.
     */
    public string $website = '';

    #[Locked]
    public int $renderedAt = 0;

    public bool $isSubmitted = false;

    public function mount(): void
    {
        $this->renderedAt = now()->getTimestamp();

        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
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
                            ->maxLength(190),
                        TextInput::make('company')
                            ->label('Label ou structure')
                            ->autocomplete('organization')
                            ->required()
                            ->maxLength(120),
                        Select::make('role')
                            ->label('Vous êtes')
                            ->options(DemoRequesterRole::class)
                            ->native()
                            ->required(),
                        Select::make('catalogue_size')
                            ->label('Taille du catalogue')
                            ->options(CatalogueSize::class)
                            ->native()
                            ->columnSpanFull(),
                        Textarea::make('message')
                            ->label('Ce que vous voulez voir pendant la démo')
                            ->placeholder('Ex. : import de nos rapports distributeurs, relevés pour 30 artistes…')
                            ->rows(4)
                            ->maxLength(2000)
                            ->columnSpanFull(),
                        Checkbox::make('consent')
                            ->label(new HtmlString(
                                'J’accepte que VibeVault utilise ces informations pour me recontacter au sujet de ma demande. '
                                .'<a href="'.e(route('legal.privacy')).'" class="underline">Politique de confidentialité</a>.'
                            ))
                            ->accepted()
                            ->validationMessages([
                                'accepted' => 'Votre accord est nécessaire pour que nous puissions vous répondre.',
                            ])
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $this->ensureIsNotRateLimited();

        $state = $this->form->getState();

        RateLimiter::hit($this->throttleKey(), 3600);

        if ($this->isLikelyBot()) {
            $this->isSubmitted = true;

            return;
        }

        $demoRequest = DemoRequest::create([
            'name' => $state['name'],
            'email' => $state['email'],
            'company' => $state['company'],
            'role' => $state['role'],
            'catalogue_size' => $state['catalogue_size'] ?? null,
            'message' => $state['message'] ?? null,
            'consented_at' => now(),
            'ip_hash' => DemoRequest::hashIp(request()->ip()),
        ]);

        $this->notifySales($demoRequest);

        $this->form->fill();
        $this->isSubmitted = true;
    }

    public function render(): View
    {
        return view('livewire.demo-request-form');
    }

    /**
     * Bots get a fake success so they learn nothing; nothing is stored.
     */
    protected function isLikelyBot(): bool
    {
        $minimumSeconds = config('marketing.demo_requests.minimum_fill_seconds');
        $isTooFast = (now()->getTimestamp() - $this->renderedAt) < $minimumSeconds;

        return $this->website !== '' || $isTooFast;
    }

    protected function ensureIsNotRateLimited(): void
    {
        $maxAttempts = config('marketing.demo_requests.max_attempts_per_hour');

        if (! RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return;
        }

        $minutes = (int) ceil(RateLimiter::availableIn($this->throttleKey()) / 60);

        throw ValidationException::withMessages([
            'data.email' => "Trop de demandes envoyées depuis votre connexion. Réessayez dans {$minutes} min.",
        ]);
    }

    protected function throttleKey(): string
    {
        return 'demo-request:'.DemoRequest::hashIp(request()->ip());
    }

    protected function notifySales(DemoRequest $demoRequest): void
    {
        $notifyEmail = config('marketing.demo_requests.notify_email');

        if (blank($notifyEmail)) {
            return;
        }

        Notification::route('mail', $notifyEmail)->notify(new DemoRequestReceived($demoRequest));
    }
}
