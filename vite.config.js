import { defineConfig } from 'vite';

export default defineConfig({
    build: {
        outDir: 'resources/dist',
        emptyOutDir: true,
        lib: {
            entry: 'resources/js/index.js',
            name: 'FilamentPasskeys',
            formats: ['iife'],
            fileName: () => 'filament-passkeys.js',
        },
        minify: true,
    },
});
