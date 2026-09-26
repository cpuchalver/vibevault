<x-layouts.marketing>
    {{-- Hero --}}
    <section class="mx-auto grid max-w-6xl items-center gap-12 px-4 pt-14 pb-20 sm:px-6 lg:grid-cols-[1.05fr_1fr] lg:pt-20">
        <div>
            <h1 class="font-display text-4xl leading-[1.05] font-bold tracking-tight text-balance sm:text-5xl lg:text-[3.5rem]">
                Des relevés de royalties que vos artistes comprennent.
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-relaxed text-ink-soft">
                VibeVault réunit le catalogue, les contrats et les royalties de votre label.
                Chaque artiste dispose de son propre espace pour suivre ses sorties, ses écoutes et ce qui lui revient, ligne par ligne.
            </p>
            <div class="mt-8 flex flex-wrap items-center gap-3">
                <a href="{{ route('demo') }}" class="rounded-md bg-signal px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-signal-deep">
                    Demander une démo
                </a>
                <a href="{{ route('pricing') }}" class="rounded-md border border-ink/20 px-5 py-3 text-sm font-semibold transition-colors hover:border-ink">
                    Voir les tarifs
                </a>
            </div>
        </div>

        <x-marketing.royalty-statement class="lg:translate-y-2" />
    </section>

    {{-- Two sides of the same catalogue --}}
    <section class="border-y border-rule bg-surface">
        <div class="mx-auto grid max-w-6xl md:grid-cols-2">
            <div class="px-4 py-14 sm:px-6 md:border-r md:border-rule md:pr-12">
                <h2 class="font-display text-2xl font-bold tracking-tight">Pour le label</h2>
                <p class="mt-3 max-w-md text-ink-soft">Une seule source de vérité pour ce que vous publiez, ce que vous devez et à qui.</p>
                <ul class="mt-6 space-y-3 text-sm">
                    <li class="flex gap-3"><x-marketing.tick /> Sorties, titres, ISRC, UPC et crédits au même endroit</li>
                    <li class="flex gap-3"><x-marketing.tick /> Contrats avec taux, territoires et avances à recouper</li>
                    <li class="flex gap-3"><x-marketing.tick /> Import des rapports de vos distributeurs</li>
                    <li class="flex gap-3"><x-marketing.tick /> Relevés calculés et publiés en un clic</li>
                </ul>
            </div>
            <div class="border-t border-rule px-4 py-14 sm:px-6 md:border-t-0 md:pl-12">
                <h2 class="font-display text-2xl font-bold tracking-tight">Pour l’artiste</h2>
                <p class="mt-3 max-w-md text-ink-soft">Un espace personnel, sans tableur ni email pour demander où en sont les comptes.</p>
                <ul class="mt-6 space-y-3 text-sm">
                    <li class="flex gap-3"><x-marketing.tick /> Écoutes et revenus par titre, plateforme et pays</li>
                    <li class="flex gap-3"><x-marketing.tick /> Calendrier des prochaines sorties</li>
                    <li class="flex gap-3"><x-marketing.tick /> Contrats et avenants consultables à tout moment</li>
                    <li class="flex gap-3"><x-marketing.tick /> Relevés détaillés, avance restante comprise</li>
                </ul>
            </div>
        </div>
    </section>

    {{-- Workflow: this is a real sequence --}}
    <section class="mx-auto max-w-6xl px-4 py-20 sm:px-6">
        <h2 class="max-w-2xl font-display text-3xl font-bold tracking-tight text-balance">
            De la sortie au relevé, sans ressaisie
        </h2>
        <ol class="mt-12 grid gap-10 sm:grid-cols-2 lg:grid-cols-4 lg:gap-8">
            @foreach([
                ['title' => 'Déclarez la sortie', 'body' => 'Titres, codes ISRC et UPC, crédits et répartition des parts entre les ayants droit.'],
                ['title' => 'Importez les ventes', 'body' => 'Déposez les rapports de vos distributeurs. Chaque ligne est rattachée au bon titre.'],
                ['title' => 'Appliquez les contrats', 'body' => 'Taux par support et par territoire, avances et frais recoupables, calculés automatiquement.'],
                ['title' => 'Publiez le relevé', 'body' => 'L’artiste est prévenu et retrouve son relevé détaillé dans son espace.'],
            ] as $step)
                <li class="border-t-2 border-ink pt-4">
                    <span class="font-display text-sm font-bold text-accent">Étape {{ $loop->iteration }}</span>
                    <h3 class="mt-2 font-display text-lg font-bold">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink-soft">{{ $step['body'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Data isolation --}}
    <section class="bg-band text-on-band">
        <div class="mx-auto grid max-w-6xl gap-12 px-4 py-20 sm:px-6 lg:grid-cols-[1fr_1.2fr]">
            <div>
                <h2 class="font-display text-3xl font-bold tracking-tight text-balance">
                    Les données d’un label restent celles de ce label.
                </h2>
                <p class="mt-4 max-w-md leading-relaxed text-on-band/75">
                    Contrats, montants et coordonnées bancaires sont des informations sensibles. VibeVault les cloisonne par conception, pas par convention.
                </p>
            </div>
            <dl class="grid gap-x-10 gap-y-8 sm:grid-cols-2">
                <div>
                    <dt class="font-semibold">Isolation à chaque requête</dt>
                    <dd class="mt-1.5 text-sm leading-relaxed text-on-band/70">Chaque lecture et chaque écriture est limitée au label connecté. Un artiste ne voit que ses propres données.</dd>
                </div>
                <div>
                    <dt class="font-semibold">Rôles précis</dt>
                    <dd class="mt-1.5 text-sm leading-relaxed text-on-band/70">Administration, A&amp;R, comptabilité, artiste : chacun n’accède qu’à ce dont il a besoin.</dd>
                </div>
                <div>
                    <dt class="font-semibold">Journal d’accès</dt>
                    <dd class="mt-1.5 text-sm leading-relaxed text-on-band/70">Consultations de contrats et publications de relevés sont tracées et consultables.</dd>
                </div>
                <div>
                    <dt class="font-semibold">Conforme au RGPD</dt>
                    <dd class="mt-1.5 text-sm leading-relaxed text-on-band/70">Données minimisées, export complet à tout moment et suppression à la résiliation.</dd>
                </div>
            </dl>
        </div>
    </section>

    <x-marketing.cta />
</x-layouts.marketing>
