<button
    type="button"
    data-theme-toggle
    class="flex size-9 items-center justify-center rounded-md border border-rule text-ink-soft transition-colors hover:border-ink/40 hover:text-ink"
    aria-label="Activer le thème sombre"
    data-label-light="Activer le thème sombre"
    data-label-dark="Activer le thème clair"
>
    {{-- Moon: shown in light mode --}}
    <svg class="size-4.5 dark:hidden" viewBox="0 0 20 20" fill="none" aria-hidden="true">
        <path d="M16.5 12.4A7 7 0 0 1 7.6 3.5a7 7 0 1 0 8.9 8.9Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
    </svg>
    {{-- Sun: shown in dark mode --}}
    <svg class="hidden size-4.5 dark:block" viewBox="0 0 20 20" fill="none" aria-hidden="true">
        <circle cx="10" cy="10" r="3.5" stroke="currentColor" stroke-width="1.6"/>
        <path d="M10 1.5v2M10 16.5v2M1.5 10h2M16.5 10h2M4 4l1.4 1.4M14.6 14.6 16 16M4 16l1.4-1.4M14.6 5.4 16 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
    </svg>
</button>
