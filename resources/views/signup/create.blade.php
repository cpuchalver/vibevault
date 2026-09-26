@php
    $trialDays = config('marketing.trial_days');
@endphp

<x-layouts.marketing title="Créer votre espace label" description="Créez l’espace VibeVault de votre label et essayez-le gratuitement pendant {{ $trialDays }} jours." :with-forms="true">
    <div class="mx-auto grid max-w-6xl gap-12 px-4 pt-14 sm:px-6 lg:grid-cols-[1fr_1.3fr] lg:gap-16 lg:pt-20">
        <div>
            <h1 class="font-display text-4xl leading-[1.08] font-bold tracking-tight text-balance sm:text-5xl">
                {{ $trialDays }} jours pour tout essayer.
            </h1>
            <p class="mt-5 max-w-lg text-lg leading-relaxed text-ink-soft">
                Créez votre compte et votre espace label. Vous enregistrerez ensuite un moyen de paiement sur la page sécurisée de Stripe : rien n’est prélevé avant la fin de l’essai.
            </p>

            <h2 class="mt-12 font-display text-lg font-bold">Ce qui se passe ensuite</h2>
            <ol class="mt-5 space-y-6">
                @foreach([
                    ['title' => 'Confirmation de votre email', 'body' => 'Un lien vous est envoyé par email : il active votre compte.'],
                    ['title' => 'Paiement sécurisé par Stripe', 'body' => 'Carte ou prélèvement SEPA. Ajoutez votre numéro de TVA intracommunautaire pour une facture en autoliquidation.'],
                    ['title' => 'Essai de '.$trialDays.' jours', 'body' => 'Annulable à tout moment depuis la page Facturation, sans frais.'],
                ] as $step)
                    <li class="grid grid-cols-[2rem_1fr] gap-3">
                        <span class="flex size-8 items-center justify-center rounded-full border border-ink/20 font-display text-sm font-bold">{{ $loop->iteration }}</span>
                        <div>
                            <h3 class="font-semibold">{{ $step['title'] }}</h3>
                            <p class="mt-1 text-sm leading-relaxed text-ink-soft">{{ $step['body'] }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <p class="mt-10 text-sm text-slate">
                Déjà client ?
                <a href="{{ route('filament.label.auth.login') }}" class="font-semibold text-accent underline underline-offset-4">Se connecter</a>
            </p>
        </div>

        <div>
            <livewire:signup-form />
        </div>
    </div>
</x-layouts.marketing>
