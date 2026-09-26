<x-layouts.marketing title="Bienvenue">
    <div class="mx-auto max-w-2xl px-4 pt-14 sm:px-6 lg:pt-20">
        <div role="status" class="rounded-xl border border-rule bg-surface p-8 sm:p-10">
            <x-marketing.logo-mark class="size-10" />
            <h1 class="mt-5 font-display text-3xl font-bold tracking-tight">Bienvenue sur VibeVault</h1>
            <p class="mt-4 leading-relaxed text-ink-soft">
                Merci ! Votre moyen de paiement est enregistré et l’espace de <strong class="text-ink">{{ $label->name }}</strong> est prêt.
                En période d’essai, rien n’est prélevé avant son terme.
            </p>

            @if(! auth()->user()->hasVerifiedEmail())
                <p class="mt-4 leading-relaxed text-ink-soft">
                    Dernière étape : ouvrez le lien de confirmation envoyé à <strong class="text-ink">{{ auth()->user()->email }}</strong> pour accéder à votre espace.
                </p>
            @endif

            <a href="{{ $panelUrl }}" class="mt-8 inline-block rounded-md bg-signal px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-signal-deep">
                Accéder à mon espace label
            </a>
        </div>
    </div>
</x-layouts.marketing>
