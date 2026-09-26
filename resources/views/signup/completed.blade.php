<x-layouts.marketing title="Activation de votre abonnement" :refresh-seconds="3">
    <div class="mx-auto max-w-2xl px-4 pt-14 sm:px-6 lg:pt-20">
        <div role="status" class="rounded-xl border border-rule bg-surface p-8 sm:p-10">
            <x-marketing.logo-mark class="size-10" />
            <h1 class="mt-5 font-display text-3xl font-bold tracking-tight">Paiement enregistré</h1>
            <p class="mt-4 leading-relaxed text-ink-soft">
                Merci ! Nous activons l’abonnement de <strong class="text-ink">{{ $label->name }}</strong>.
                Vous allez être redirigé vers votre tableau de bord dans quelques secondes.
            </p>
            <p class="mt-4 text-sm text-slate">
                Si rien ne se passe au bout d’une minute, <a href="{{ route('signup.completed', ['label' => $label->slug]) }}" class="font-semibold text-accent underline underline-offset-4">rechargez cette page</a> ou contactez-nous.
            </p>
        </div>
    </div>
</x-layouts.marketing>
