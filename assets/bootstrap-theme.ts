if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.setAttribute('data-bs-theme', 'dark');
} else {
    document.documentElement.setAttribute('data-bs-theme', 'light');
}
