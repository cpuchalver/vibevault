@php
    $plans = config('marketing.plans');
    $yearlyFreeMonths = config('marketing.yearly_free_months');
    $euros = fn (int $amount): string => number_format($amount, 0, ',', "\u{202F}").' €';
@endphp

<x-layouts.marketing title="Tarifs" description="Les formules VibeVault pour les labels indépendants, les catalogues actifs et les groupes de labels. Portail artiste inclus dans chaque formule.">
    <div class="group/billing">
        <header class="mx-auto max-w-6xl px-4 pt-14 pb-10 sm:px-6 lg:pt-20">
            <h1 class="max-w-3xl font-display text-4xl leading-[1.08] font-bold tracking-tight text-balance sm:text-5xl">
                Un tarif par label, quel que soit le nombre de relevés.
            </h1>
            <p class="mt-5 max-w-2xl text-lg leading-relaxed text-ink-soft">
                Le portail artiste est inclus dans chaque formule. Prix hors taxes, sans frais de mise en service.
            </p>

            <fieldset class="mt-8 inline-flex rounded-lg border border-rule bg-white p-1 text-sm font-medium">
                <legend class="sr-only">Période de facturation</legend>
                <label class="cursor-pointer rounded-md px-4 py-2 has-checked:bg-ink has-checked:text-white has-focus-visible:outline-2 has-focus-visible:outline-signal">
                    <input type="radio" name="billing" value="monthly" class="sr-only" checked>
                    Mensuel
                </label>
                <label class="cursor-pointer rounded-md px-4 py-2 has-checked:bg-ink has-checked:text-white has-focus-visible:outline-2 has-focus-visible:outline-signal">
                    <input type="radio" name="billing" value="yearly" id="billing-yearly" class="sr-only">
                    Annuel, {{ $yearlyFreeMonths }} mois offerts
                </label>
            </fieldset>
        </header>

        <section aria-label="Formules" class="mx-auto grid max-w-6xl gap-6 px-4 sm:px-6 lg:grid-cols-3">
            @foreach($plans as $plan)
                <article @class([
                    'flex flex-col rounded-xl border bg-white p-7',
                    'border-ink ring-1 ring-ink' => $plan['highlighted'],
                    'border-rule' => ! $plan['highlighted'],
                ])>
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 class="font-display text-xl font-bold">{{ $plan['name'] }}</h2>
                        @if($plan['highlighted'])
                            <span class="rounded-full bg-meter/25 px-2.5 py-0.5 text-xs font-semibold text-ink">Le plus choisi</span>
                        @endif
                    </div>
                    <p class="mt-2 text-sm text-ink-soft lg:min-h-10">{{ $plan['audience'] }}</p>

                    <div class="figures mt-6 min-h-[4.5rem]">
                        @if($plan['monthly_price'] === null)
                            <p class="font-display text-4xl font-bold tracking-tight">Sur devis</p>
                            <p class="mt-1 text-sm text-slate">Selon le nombre de labels</p>
                        @else
                            <div class="group-has-[#billing-yearly:checked]/billing:hidden">
                                <p><span class="font-display text-4xl font-bold tracking-tight">{{ $euros($plan['monthly_price']) }}</span> <span class="text-sm text-slate">HT / mois</span></p>
                                <p class="mt-1 text-sm text-slate">Sans engagement</p>
                            </div>
                            <div class="hidden group-has-[#billing-yearly:checked]/billing:block">
                                <p><span class="font-display text-4xl font-bold tracking-tight">{{ $euros($plan['monthly_price'] * (12 - $yearlyFreeMonths)) }}</span> <span class="text-sm text-slate">HT / an</span></p>
                                <p class="mt-1 text-sm text-slate">Au lieu de {{ $euros($plan['monthly_price'] * 12) }}</p>
                            </div>
                        @endif
                    </div>

                    <ul class="mt-6 flex-1 space-y-3 border-t border-rule pt-6 text-sm">
                        @foreach($plan['features'] as $feature)
                            <li class="flex gap-3"><x-marketing.tick /> {{ $feature }}</li>
                        @endforeach
                    </ul>

                    <a
                        href="{{ route('demo') }}"
                        @class([
                            'mt-8 rounded-md px-4 py-2.5 text-center text-sm font-semibold transition-colors',
                            'bg-signal text-white hover:bg-signal-deep' => $plan['highlighted'],
                            'border border-ink/20 hover:border-ink' => ! $plan['highlighted'],
                        ])
                    >
                        {{ $plan['monthly_price'] === null ? 'Nous contacter' : 'Demander une démo' }}
                    </a>
                </article>
            @endforeach
        </section>
    </div>

    <section class="mx-auto max-w-3xl px-4 pt-24 sm:px-6" aria-labelledby="faq-titre">
        <h2 id="faq-titre" class="font-display text-3xl font-bold tracking-tight">Questions fréquentes</h2>
        <div class="mt-8 divide-y divide-rule border-y border-rule">
            @foreach(config('marketing.faq') as $item)
                <details class="group py-5">
                    <summary class="flex cursor-pointer list-none items-start justify-between gap-6 font-semibold [&::-webkit-details-marker]:hidden">
                        {{ $item['question'] }}
                        <span class="mt-0.5 text-xl leading-none text-slate transition-transform group-open:rotate-45" aria-hidden="true">+</span>
                    </summary>
                    <p class="mt-3 max-w-2xl leading-relaxed text-ink-soft">{{ $item['answer'] }}</p>
                </details>
            @endforeach
        </div>
    </section>

    <x-marketing.cta title="Pas sûr de la formule ?" body="Décrivez votre catalogue et vos distributeurs : nous vous indiquons la formule adaptée pendant la démo." />
</x-layouts.marketing>
