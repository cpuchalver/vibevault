<div>
    @if($isSubmitted)
        <div role="status" class="rounded-xl border border-rule bg-white p-8">
            <x-marketing.logo-mark class="size-10" />
            <h2 class="mt-5 font-display text-2xl font-bold tracking-tight">Demande envoyée</h2>
            <p class="mt-3 leading-relaxed text-ink-soft">
                Merci. Nous revenons vers vous par email pour convenir d’un créneau.
                Pour gagner du temps, préparez un export récent d’un de vos distributeurs.
            </p>
            <a href="{{ route('features') }}" class="mt-6 inline-block text-sm font-semibold text-signal underline underline-offset-4">
                Parcourir les fonctionnalités en attendant
            </a>
        </div>
    @else
        <form wire:submit="submit" class="relative rounded-xl border border-rule bg-white p-6 sm:p-8" novalidate>
            {{ $this->form }}

            {{-- Honeypot: hidden from people and assistive technologies. --}}
            <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
                <label for="website">Site web</label>
                <input type="text" id="website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            <div class="mt-6 flex flex-col-reverse items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs leading-relaxed text-slate">Réponse par email. Vos données ne sont jamais revendues.</p>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                    class="shrink-0 rounded-md bg-signal px-5 py-3 text-sm font-semibold whitespace-nowrap text-white transition-colors hover:bg-signal-deep disabled:cursor-wait disabled:opacity-70"
                >
                    <span wire:loading.remove wire:target="submit">Envoyer ma demande</span>
                    <span wire:loading wire:target="submit">Envoi en cours…</span>
                </button>
            </div>
        </form>

        <x-filament-actions::modals />
    @endif
</div>
