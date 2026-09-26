@php
    $modules = [
        [
            'id' => 'catalogue',
            'title' => 'Catalogue',
            'intro' => 'Toutes vos sorties et leurs métadonnées, prêtes pour la distribution comme pour le calcul des droits.',
            'items' => [
                ['Sorties et titres', 'Albums, EP et singles, avec date de sortie, label, format et visuels.'],
                ['Identifiants', 'ISRC par titre, UPC par sortie, contrôlés au format pour éviter les doublons.'],
                ['Crédits et ayants droit', 'Interprètes, auteurs, compositeurs, producteurs et leur part sur chaque titre.'],
                ['Historique', 'Chaque modification de métadonnée est horodatée avec son auteur.'],
            ],
        ],
        [
            'id' => 'contrats',
            'title' => 'Contrats',
            'intro' => 'Les termes de chaque contrat, traduits en règles que les calculs appliquent sans interprétation.',
            'items' => [
                ['Taux par support', 'Streaming, téléchargement, physique, synchronisation : un taux pour chaque source de revenus.'],
                ['Territoires', 'Des taux différents selon les pays, ou des exclusions de territoire.'],
                ['Avances et frais', 'Avances versées, frais de production ou de promotion recoupables, suivi du solde.'],
                ['Documents', 'Contrat signé et avenants joints, consultables par l’artiste concerné uniquement.'],
            ],
        ],
        [
            'id' => 'royalties',
            'title' => 'Royalties',
            'intro' => 'Des rapports distributeurs au relevé publié, avec le détail que l’artiste est en droit d’attendre.',
            'items' => [
                ['Import des rapports', 'Dépôt des exports CSV des distributeurs, rapprochement automatique par ISRC et UPC.'],
                ['Lignes non rattachées', 'Les ventes sans correspondance sont isolées pour que rien ne soit perdu.'],
                ['Calcul des parts', 'Application des taux, des répartitions entre ayants droit et du recoupement des avances.'],
                ['Relevés', 'Relevés mensuels ou trimestriels, téléchargeables en PDF et en CSV.'],
            ],
        ],
        [
            'id' => 'portail-artiste',
            'title' => 'Portail artiste',
            'intro' => 'Un espace à l’image de votre label où l’artiste trouve seul les réponses à ses questions.',
            'items' => [
                ['Performances', 'Écoutes et revenus par titre, par plateforme et par pays, période par période.'],
                ['Sorties', 'Calendrier des sorties à venir et fiche de chaque sortie publiée.'],
                ['Contrats', 'Consultation des contrats et du solde d’avance restant à recouper.'],
                ['Relevés', 'Notification à chaque nouveau relevé, historique complet téléchargeable.'],
            ],
        ],
        [
            'id' => 'equipe-securite',
            'title' => 'Équipe et sécurité',
            'intro' => 'Les bons accès pour chaque personne, et la trace de ce qui a été consulté.',
            'items' => [
                ['Rôles', 'Administration, A&R, comptabilité, lecture seule : des rôles prêts à l’emploi, ajustables.'],
                ['Cloisonnement', 'Les données de chaque label sont isolées ; un artiste n’accède qu’aux siennes.'],
                ['Journal d’accès', 'Consultation des contrats, publication des relevés et exports sont tracés.'],
                ['Export et suppression', 'Export complet de vos données à tout moment, suppression à la résiliation.'],
            ],
        ],
    ];
@endphp

<x-layouts.marketing title="Fonctionnalités" description="Catalogue, contrats, royalties, portail artiste et gestion des accès : le détail des fonctionnalités de VibeVault.">
    <header class="mx-auto max-w-6xl px-4 pt-14 pb-10 sm:px-6 lg:pt-20">
        <h1 class="max-w-3xl font-display text-4xl leading-[1.08] font-bold tracking-tight text-balance sm:text-5xl">
            Tout ce dont un label a besoin pour rendre des comptes justes.
        </h1>
        <p class="mt-5 max-w-2xl text-lg leading-relaxed text-ink-soft">
            Cinq modules qui partagent les mêmes données : ce que vous saisissez dans le catalogue sert au calcul des royalties et apparaît dans l’espace de l’artiste.
        </p>
    </header>

    <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:px-6 lg:grid-cols-[13rem_1fr] lg:gap-16">
        <nav aria-label="Modules" class="hidden lg:block">
            <ul class="sticky top-24 space-y-1 border-l border-rule text-sm">
                @foreach($modules as $module)
                    <li>
                        <a href="#{{ $module['id'] }}" class="-ml-px block border-l-2 border-transparent py-1.5 pl-4 text-ink-soft hover:border-signal hover:text-ink">
                            {{ $module['title'] }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        <div class="divide-y divide-rule">
            @foreach($modules as $module)
                <section id="{{ $module['id'] }}" aria-labelledby="{{ $module['id'] }}-titre" class="py-12 first:pt-0">
                    <h2 id="{{ $module['id'] }}-titre" class="font-display text-2xl font-bold tracking-tight sm:text-3xl">{{ $module['title'] }}</h2>
                    <p class="mt-3 max-w-2xl text-ink-soft">{{ $module['intro'] }}</p>
                    <dl class="mt-8 grid gap-x-10 gap-y-6 sm:grid-cols-2">
                        @foreach($module['items'] as [$term, $description])
                            <div>
                                <dt class="flex gap-2.5 font-semibold"><x-marketing.tick /> {{ $term }}</dt>
                                <dd class="mt-1.5 pl-6.5 text-sm leading-relaxed text-ink-soft">{{ $description }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </section>
            @endforeach
        </div>
    </div>

    <x-marketing.cta />
</x-layouts.marketing>
