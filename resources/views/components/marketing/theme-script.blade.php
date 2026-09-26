{{--
    Applies the saved or system theme before first paint to avoid a flash.
    Uses Filament's `theme` localStorage key ("light" | "dark" | "system")
    so the preference is shared with the future application panels.
    Must stay inline: a CSP will need to allow it with a nonce.
--}}
<script>
    (() => {
        let theme = 'system';

        try {
            theme = localStorage.getItem('theme') ?? 'system';
        } catch {}

        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = theme === 'dark' || (theme === 'system' && prefersDark);

        document.documentElement.classList.toggle('dark', isDark);
    })();
</script>
