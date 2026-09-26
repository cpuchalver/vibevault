<div>
    <form wire:submit="submit" class="relative rounded-xl border border-rule bg-surface p-6 sm:p-8" novalidate>
        {{ $this->form }}

        {{-- Honeypot: hidden from people and assistive technologies. --}}
        <div class="absolute -left-[9999px] h-px w-px overflow-hidden" aria-hidden="true">
            <label for="website">Site web</label>
            <input type="text" id="website" wire:model="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="mt-6 flex flex-col-reverse items-start gap-4 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-xs leading-relaxed text-slate">Vous allez être redirigé vers Stripe pour enregistrer votre moyen de paiement.</p>
            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="submit"
                class="shrink-0 rounded-md bg-signal px-5 py-3 text-sm font-semibold whitespace-nowrap text-white transition-colors hover:bg-signal-deep disabled:cursor-wait disabled:opacity-70"
            >
                <span wire:loading.remove wire:target="submit">Créer mon compte</span>
                <span wire:loading wire:target="submit">Création en cours…</span>
            </button>
        </div>
    </form>

    <x-filament-actions::modals />
</div>
