import { ref, watch, onMounted } from 'vue';

const theme = ref(
    typeof window !== 'undefined'
        ? localStorage.getItem('theme') || 'light'
        : 'light'
);

export function useTheme() {
    const toggleTheme = () => {
        theme.value = theme.value === 'light' ? 'dark' : 'light';
    };

    const applyTheme = (themeValue: string) => {
        if (themeValue === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    onMounted(() => {
        applyTheme(theme.value);
    });

    watch(theme, (newTheme) => {
        localStorage.setItem('theme', newTheme);
        applyTheme(newTheme);
    });

    return { theme, toggleTheme };
}
