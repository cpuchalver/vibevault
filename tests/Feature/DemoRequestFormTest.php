<?php

declare(strict_types=1);

use App\Enums\CatalogueSize;
use App\Enums\DemoRequesterRole;
use App\Livewire\DemoRequestForm;
use App\Models\DemoRequest;
use App\Notifications\DemoRequestReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('marketing.demo_requests.notify_email', 'sales@vibevault.test');
    config()->set('marketing.demo_requests.max_attempts_per_hour', 3);
    config()->set('marketing.demo_requests.minimum_fill_seconds', 3);

    RateLimiter::clear('demo-request:'.DemoRequest::hashIp('127.0.0.1'));

    Notification::fake();
});

/**
 * @return array<string, mixed>
 */
function validDemoRequest(array $overrides = []): array
{
    return [
        'name' => 'Camille Durand',
        'email' => 'camille@label-test.fr',
        'company' => 'Label Test',
        'role' => DemoRequesterRole::Label->value,
        'catalogue_size' => CatalogueSize::Medium->value,
        'message' => 'Import de nos rapports distributeurs.',
        'consent' => true,
        ...$overrides,
    ];
}

function mountedDemoForm(): Testable
{
    $component = Livewire::test(DemoRequestForm::class);

    test()->travel(5)->seconds();

    return $component;
}

it('stores a valid demo request and notifies sales', function (): void {
    mountedDemoForm()
        ->fillForm(validDemoRequest())
        ->call('submit')
        ->assertHasNoFormErrors()
        ->assertSet('isSubmitted', true)
        ->assertSee('Demande envoyée');

    $demoRequest = DemoRequest::sole();

    expect($demoRequest)
        ->email->toBe('camille@label-test.fr')
        ->role->toBe(DemoRequesterRole::Label)
        ->catalogue_size->toBe(CatalogueSize::Medium)
        ->consented_at->not->toBeNull()
        ->ip_hash->toBe(DemoRequest::hashIp('127.0.0.1'))
        ->ip_hash->not->toContain('127.0.0.1');

    Notification::assertSentOnDemand(
        DemoRequestReceived::class,
        fn (DemoRequestReceived $notification, array $channels, AnonymousNotifiable $notifiable): bool => $notifiable->routes['mail'] === 'sales@vibevault.test'
            && $notification->demoRequest->is($demoRequest),
    );
});

it('does not notify when no sales address is configured', function (): void {
    config()->set('marketing.demo_requests.notify_email', null);

    mountedDemoForm()
        ->fillForm(validDemoRequest())
        ->call('submit')
        ->assertHasNoFormErrors();

    expect(DemoRequest::count())->toBe(1);
    Notification::assertNothingSent();
});

it('requires explicit consent', function (): void {
    mountedDemoForm()
        ->fillForm(validDemoRequest(['consent' => false]))
        ->call('submit')
        ->assertHasFormErrors(['consent' => 'accepted']);

    expect(DemoRequest::count())->toBe(0);
});

it('validates the submitted fields', function (array $overrides, array $errors): void {
    mountedDemoForm()
        ->fillForm(validDemoRequest($overrides))
        ->call('submit')
        ->assertHasFormErrors($errors);

    expect(DemoRequest::count())->toBe(0);
})->with([
    'missing name' => [['name' => ''], ['name' => 'required']],
    'invalid email' => [['email' => 'not-an-email'], ['email' => 'email']],
    'missing company' => [['company' => ''], ['company' => 'required']],
    'unknown role' => [['role' => 'hacker'], ['role']],
    'overlong message' => [['message' => str_repeat('a', 2001)], ['message' => 'max']],
]);

it('silently drops submissions that fill the honeypot', function (): void {
    mountedDemoForm()
        ->set('website', 'https://spam.example')
        ->fillForm(validDemoRequest())
        ->call('submit')
        ->assertSet('isSubmitted', true);

    expect(DemoRequest::count())->toBe(0);
    Notification::assertNothingSent();
});

it('silently drops submissions sent faster than a human could type', function (): void {
    Livewire::test(DemoRequestForm::class)
        ->fillForm(validDemoRequest())
        ->call('submit')
        ->assertSet('isSubmitted', true);

    expect(DemoRequest::count())->toBe(0);
});

it('prevents tampering with the render timestamp', function (): void {
    Livewire::test(DemoRequestForm::class)
        ->set('renderedAt', 0);
})->throws(CannotUpdateLockedPropertyException::class);

it('rate limits submissions per visitor', function (): void {
    foreach (range(1, 3) as $attempt) {
        mountedDemoForm()
            ->fillForm(validDemoRequest(['email' => "prospect{$attempt}@label-test.fr"]))
            ->call('submit')
            ->assertHasNoFormErrors();
    }

    mountedDemoForm()
        ->fillForm(validDemoRequest(['email' => 'prospect4@label-test.fr']))
        ->call('submit')
        ->assertHasErrors(['data.email']);

    expect(DemoRequest::count())->toBe(3);
});

it('escapes prospect input in the sales email', function (): void {
    $demoRequest = DemoRequest::factory()->create([
        'company' => '[Cliquez ici](https://phishing.example)',
        'message' => '**urgent**',
    ]);

    $mail = (new DemoRequestReceived($demoRequest))->toMail(new AnonymousNotifiable);
    $rendered = (string) $mail->render();

    expect($rendered)
        ->not->toContain('href="https://phishing.example"')
        ->not->toContain('<strong>urgent</strong>');
});

it('prunes demo requests after the retention period', function (): void {
    config()->set('marketing.demo_requests.retention_months', 24);

    $expired = DemoRequest::factory()->create(['created_at' => now()->subMonths(25)]);
    $recent = DemoRequest::factory()->create(['created_at' => now()->subMonths(23)]);

    $this->artisan('model:prune', ['--model' => [DemoRequest::class]])->assertSuccessful();

    expect(DemoRequest::find($expired->id))->toBeNull()
        ->and(DemoRequest::find($recent->id))->not->toBeNull();
});

it('shows validation errors to a fast visitor instead of a fake success', function (): void {
    Livewire::test(DemoRequestForm::class)
        ->fillForm(validDemoRequest(['email' => '']))
        ->call('submit')
        ->assertHasFormErrors(['email' => 'required'])
        ->assertSet('isSubmitted', false);
});
