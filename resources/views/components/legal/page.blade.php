@props(['title', 'description'])

@php
    $updatedAt = config('marketing.legal_updated_at');
@endphp

<x-layouts.marketing :title="$title" :description="$description">
    <article class="mx-auto max-w-3xl px-4 pt-14 sm:px-6 lg:pt-20">
        <h1 class="font-display text-4xl font-bold tracking-tight sm:text-5xl">{{ $title }}</h1>
        <p class="mt-4 text-sm text-slate">
            Dernière mise à jour :
            @if($updatedAt)
                {{ \Illuminate\Support\Carbon::parse($updatedAt)->translatedFormat('j F Y') }}
            @else
                <span class="placeholder-value">[à compléter]</span>
            @endif
        </p>

        <div class="prose-legal mt-8 max-w-[68ch]">
            {{ $slot }}
        </div>
    </article>
</x-layouts.marketing>
