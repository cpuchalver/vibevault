<x-layouts.marketing title="Demander une démo" description="Réservez une démonstration de VibeVault avec un de vos propres rapports distributeurs." :with-forms="true">
    <div class="mx-auto grid max-w-6xl gap-12 px-4 pt-14 sm:px-6 lg:grid-cols-[1fr_1.15fr] lg:gap-16 lg:pt-20">
        <div>
            <h1 class="font-display text-4xl leading-[1.08] font-bold tracking-tight text-balance sm:text-5xl">
                Une démo avec vos propres chiffres.
            </h1>
            <p class="mt-5 max-w-lg text-lg leading-relaxed text-ink-soft">
                Pas de présentation générique : nous partons de votre catalogue et d’un de vos rapports distributeurs.
            </p>

            <h2 class="mt-12 font-display text-lg font-bold">Comment ça se passe</h2>
            <ol class="mt-5 space-y-6">
                @foreach([
                    ['title' => 'Nous vous recontactons', 'body' => 'Par email, pour fixer un créneau de 30 minutes en visioconférence.'],
                    ['title' => 'Vous partagez un export', 'body' => 'Un rapport distributeur récent, que nous importons devant vous puis supprimons après la démo.'],
                    ['title' => 'Vous voyez le résultat', 'body' => 'Les relevés que recevraient vos artistes, et l’espace dans lequel ils les consulteraient.'],
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
        </div>

        <div>
            <livewire:demo-request-form />
        </div>
    </div>
</x-layouts.marketing>
