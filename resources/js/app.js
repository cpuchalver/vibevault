const storageKey = 'theme';
const darkQuery = window.matchMedia('(prefers-color-scheme: dark)');

function readStoredTheme() {
    try {
        return localStorage.getItem(storageKey) ?? 'system';
    } catch {
        return 'system';
    }
}

function storeTheme(theme) {
    try {
        localStorage.setItem(storageKey, theme);
    } catch {
        // Storage can be unavailable (private mode): the choice lasts for the page only.
    }
}

function applyTheme(theme) {
    const isDark = theme === 'dark' || (theme === 'system' && darkQuery.matches);

    document.documentElement.classList.toggle('dark', isDark);

    document.querySelectorAll('[data-theme-toggle]').forEach(button => {
        button.setAttribute('aria-label', isDark ? button.dataset.labelDark : button.dataset.labelLight);
        button.setAttribute('aria-pressed', String(isDark));
    });
}

document.addEventListener('click', event => {
    const button = event.target.closest('[data-theme-toggle]');

    if (! button) {
        return;
    }

    const nextTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';

    storeTheme(nextTheme);
    applyTheme(nextTheme);
});

darkQuery.addEventListener('change', () => {
    if (readStoredTheme() !== 'system') {
        return;
    }

    applyTheme('system');
});

applyTheme(readStoredTheme());
