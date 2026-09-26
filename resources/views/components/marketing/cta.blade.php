@props([
    'title' => 'Voyons ensemble votre catalogue.',
    'body' => 'En 30 minutes, nous importons un de vos rapports distributeurs et vous montrons le relevé que recevraient vos artistes.',
])

<section class="mx-auto max-w-6xl px-4 pt-20 sm:px-6">
    <div class="flex flex-col items-start justify-between gap-8 rounded-2xl border border-rule bg-surface px-6 py-10 sm:px-10 md:flex-row md:items-center">
        <div class="max-w-xl">
            <h2 class="font-display text-2xl font-bold tracking-tight sm:text-3xl">{{ $title }}</h2>
            <p class="mt-3 leading-relaxed text-ink-soft">{{ $body }}</p>
        </div>
        <a href="{{ route('demo') }}" class="shrink-0 rounded-md bg-signal px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-signal-deep">
            Demander une démo
        </a>
    </div>
</section>
