@props([
    'title' => null,
    'description' => 'VibeVault centralise le catalogue, les contrats et les royalties de votre label, et offre à chaque artiste un espace sécurisé pour suivre ses sorties et ses relevés.',
    'withForms' => false,
])

@php
    $pageTitle = $title ? "{$title} · VibeVault" : 'VibeVault · Catalogue, contrats et royalties pour labels';
    $navigation = [
        ['route' => 'features', 'label' => 'Fonctionnalités'],
        ['route' => 'pricing', 'label' => 'Tarifs'],
    ];
    $hasOnlineSignup = app(\App\Billing\PlanCatalog::class)->selfServeOptions() !== [];
    $primaryCta = $hasOnlineSignup
        ? ['url' => route('signup'), 'label' => 'Essai gratuit']
        : ['url' => route('demo'), 'label' => 'Demander une démo'];
@endphp

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $pageTitle }}</title>
        <meta name="description" content="{{ $description }}">
        <link rel="canonical" href="{{ url()->current() }}">

        <meta property="og:type" content="website">
        <meta property="og:site_name" content="VibeVault">
        <meta property="og:title" content="{{ $pageTitle }}">
        <meta property="og:description" content="{{ $description }}">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:locale" content="fr_FR">

        <meta name="color-scheme" content="light dark">
        <x-marketing.theme-script />

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @if($withForms)
            @vite('resources/css/forms.css')
            @filamentStyles
        @endif
    </head>
    <body class="min-h-screen font-sans antialiased">
        <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:rounded focus:bg-surface focus:px-3 focus:py-2">
            Aller au contenu
        </a>

        <header class="sticky top-0 z-40 border-b border-rule/80 bg-paper/90 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-6 px-4 sm:px-6">
                <a href="{{ route('home') }}" class="flex items-center gap-2.5" aria-label="VibeVault, accueil">
                    <x-marketing.logo-mark class="size-7" />
                    <span class="font-display text-lg font-bold tracking-tight">VibeVault</span>
                </a>

                <div class="flex items-center gap-3 md:gap-6">
                    <nav aria-label="Navigation principale" class="hidden items-center gap-8 md:flex">
                        @foreach($navigation as $item)
                            <a
                                href="{{ route($item['route']) }}"
                                @class([
                                    'text-sm font-medium transition-colors hover:text-accent',
                                    'text-accent' => request()->routeIs($item['route']),
                                    'text-ink-soft' => ! request()->routeIs($item['route']),
                                ])
                                @if(request()->routeIs($item['route'])) aria-current="page" @endif
                            >{{ $item['label'] }}</a>
                        @endforeach
                        <a href="{{ route('filament.label.auth.login') }}" class="text-sm font-medium text-ink-soft transition-colors hover:text-accent">Connexion</a>
                        <a href="{{ $primaryCta['url'] }}" class="rounded-md bg-inverse px-4 py-2 text-sm font-semibold text-on-inverse transition-colors hover:bg-signal hover:text-white">
                            {{ $primaryCta['label'] }}
                        </a>
                    </nav>

                    <x-marketing.theme-toggle />

                    <details class="group relative md:hidden">
                        <summary class="flex cursor-pointer list-none items-center rounded-md border border-rule px-3 py-1.5 text-sm font-medium [&::-webkit-details-marker]:hidden">
                            Menu
                        </summary>
                        <nav aria-label="Navigation mobile" class="absolute right-0 mt-2 flex w-56 flex-col gap-1 rounded-lg border border-rule bg-surface p-2 shadow-lg">
                            @foreach($navigation as $item)
                                <a href="{{ route($item['route']) }}" class="rounded px-3 py-2 text-sm hover:bg-paper">{{ $item['label'] }}</a>
                            @endforeach
                            <a href="{{ route('filament.label.auth.login') }}" class="rounded px-3 py-2 text-sm hover:bg-paper">Connexion</a>
                            <a href="{{ $primaryCta['url'] }}" class="rounded bg-inverse px-3 py-2 text-sm font-semibold text-on-inverse">{{ $primaryCta['label'] }}</a>
                        </nav>
                    </details>
                </div>
            </div>
        </header>

        <main id="contenu">
            {{ $slot }}
        </main>

        <footer class="mt-24 border-t border-rule">
            <div class="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-[1.5fr_1fr_1fr]">
                <div class="max-w-xs">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        <x-marketing.logo-mark class="size-6" />
                        <span class="font-display font-bold tracking-tight">VibeVault</span>
                    </a>
                    <p class="mt-3 text-sm leading-relaxed text-slate">
                        Catalogue, contrats et royalties pour les labels, et un espace clair pour leurs artistes.
                    </p>
                </div>
                <nav aria-label="Produit">
                    <h2 class="text-sm font-semibold">Produit</h2>
                    <ul class="mt-3 space-y-2 text-sm text-slate">
                        <li><a href="{{ route('features') }}" class="hover:text-accent">Fonctionnalités</a></li>
                        <li><a href="{{ route('pricing') }}" class="hover:text-accent">Tarifs</a></li>
                        <li><a href="{{ route('demo') }}" class="hover:text-accent">Demander une démo</a></li>
                    </ul>
                </nav>
                <nav aria-label="Informations légales">
                    <h2 class="text-sm font-semibold">Informations légales</h2>
                    <ul class="mt-3 space-y-2 text-sm text-slate">
                        <li><a href="{{ route('legal.notice') }}" class="hover:text-accent">Mentions légales</a></li>
                        <li><a href="{{ route('legal.privacy') }}" class="hover:text-accent">Politique de confidentialité</a></li>
                        <li><a href="{{ route('legal.terms') }}" class="hover:text-accent">Conditions générales d’utilisation</a></li>
                    </ul>
                </nav>
            </div>
            <div class="border-t border-rule">
                <p class="mx-auto max-w-6xl px-4 py-5 text-xs text-slate sm:px-6">
                    © {{ now()->year }} VibeVault. Tous droits réservés.
                </p>
            </div>
        </footer>

        @if($withForms)
            @filamentScripts
        @endif
    </body>
</html>
