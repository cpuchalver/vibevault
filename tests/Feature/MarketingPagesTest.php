<?php

declare(strict_types=1);

use function Pest\Laravel\get;

it('renders every public page for guests', function (string $routeName, string $heading): void {
    get(route($routeName))
        ->assertOk()
        ->assertSee($heading, escape: false);
})->with([
    'home' => ['home', 'Des relevés de royalties que vos artistes comprennent.'],
    'features' => ['features', 'Tout ce dont un label a besoin'],
    'pricing' => ['pricing', 'Un tarif par label'],
    'demo' => ['demo', 'Une démo avec vos propres chiffres.'],
    'legal notice' => ['legal.notice', 'Mentions légales'],
    'privacy' => ['legal.privacy', 'Politique de confidentialité'],
    'terms' => ['legal.terms', 'Conditions générales d’utilisation'],
]);

it('sends baseline security headers', function (): void {
    get(route('home'))
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');
});

it('shows yearly prices with the configured free months', function (): void {
    config()->set('marketing.yearly_free_months', 2);
    config()->set('marketing.plans', [
        ['key' => 'solo', 'name' => 'Solo', 'audience' => 'Test', 'monthly_price' => 50, 'highlighted' => false, 'features' => ['Un artiste']],
        ['key' => 'big', 'name' => 'Grand compte', 'audience' => 'Test', 'monthly_price' => null, 'highlighted' => true, 'features' => []],
    ]);

    $response = get(route('pricing'))->assertOk();

    $html = str_replace("\u{202F}", ' ', $response->getContent());

    expect($html)
        ->toContain('50 €')
        ->toContain('500 €')
        ->toContain('Au lieu de 600 €')
        ->toContain('Sur devis');
});

it('flags missing legal information instead of leaving it blank', function (): void {
    config()->set('marketing.company.name', null);

    get(route('legal.notice'))->assertSee('[à compléter]');
});

it('shows configured legal information', function (): void {
    config()->set('marketing.company.name', 'VibeVault SAS');
    config()->set('marketing.company.publication_director', 'Jeanne Martin');

    get(route('legal.notice'))
        ->assertSee('VibeVault SAS')
        ->assertSee('Jeanne Martin');
});

it('applies the saved theme before styles load and exposes a theme toggle', function (): void {
    $html = get(route('home'))->assertOk()->getContent();

    $themeScriptPosition = strpos($html, "localStorage.getItem('theme')");
    $stylesheetPosition = strpos($html, '<link rel="stylesheet"');

    expect($themeScriptPosition)->not->toBeFalse()
        ->and($html)->toContain('data-theme-toggle')
        ->and($html)->toContain('aria-label="Activer le thème sombre"');

    if ($stylesheetPosition !== false) {
        expect($themeScriptPosition)->toBeLessThan($stylesheetPosition);
    }
});
