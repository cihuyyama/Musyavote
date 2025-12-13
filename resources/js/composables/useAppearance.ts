import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';

function applyTheme(theme: 'light' | 'dark') {
    document.documentElement.classList.toggle('dark', theme === 'dark');
}

export function updateTheme(value: Appearance) {
    if (typeof window === 'undefined') return;

    if (value === 'system') {
        const prefersDark = window.matchMedia(
            '(prefers-color-scheme: dark)',
        ).matches;

        applyTheme(prefersDark ? 'dark' : 'light');
    } else {
        applyTheme(value);
    }
}

const setCookie = (name: string, value: string, days = 365) => {
    const maxAge = days * 24 * 60 * 60;
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax`;
};

const getStoredAppearance = (): Appearance | null => {
    return localStorage.getItem('appearance') as Appearance | null;
};

/**
 * 🔑 INIT THEME
 * Default: LIGHT
 * Tidak auto system
 */
export function initializeTheme() {
    if (typeof window === 'undefined') return;

    const saved = getStoredAppearance();

    // DEFAULT LIGHT kalau belum ada pilihan
    const initial: Appearance = saved ?? 'light';

    updateTheme(initial);

    // Dengarkan perubahan system HANYA jika user pilih system
    if (initial === 'system') {
        window
            .matchMedia('(prefers-color-scheme: dark)')
            .addEventListener('change', () => updateTheme('system'));
    }
}

const appearance = ref<Appearance>('light');

export function useAppearance() {
    onMounted(() => {
        const saved = getStoredAppearance();
        appearance.value = saved ?? 'light';
    });

    function updateAppearance(value: Appearance) {
        appearance.value = value;

        localStorage.setItem('appearance', value);
        setCookie('appearance', value);

        updateTheme(value);
    }

    return {
        appearance,
        updateAppearance,
    };
}
