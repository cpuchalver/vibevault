<x-layouts.marketing title="Finaliser votre abonnement">
    <div class="mx-auto max-w-2xl px-4 pt-14 sm:px-6 lg:pt-20">
        <div class="rounded-xl border border-rule bg-surface p-8 sm:p-10">
            <h1 class="font-display text-3xl font-bold tracking-tight">Votre abonnement n’est pas encore actif</h1>

            @if($hasFailed)
                <p role="alert" class="mt-4 rounded-md border border-rule bg-paper px-4 py-3 text-sm">
                    La page de paiement n’a pas pu être ouverte. Réessayez dans un instant ; si le problème persiste, contactez-nous.
                </p>
            @endif

            <p class="mt-4 leading-relaxed text-ink-soft">
                Le compte de <strong class="text-ink">{{ $label->name }}</strong> est créé. Enregistrez un moyen de paiement pour démarrer votre abonnement et accéder à votre espace.
            </p>

            <form method="POST" action="{{ route('signup.checkout', ['label' => $label->slug]) }}" class="mt-8">
                @csrf
                <button type="submit" class="rounded-md bg-signal px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-signal-deep">
                    Continuer vers le paiement sécurisé
                </button>
            </form>
        </div>
    </div>
</x-layouts.marketing>
