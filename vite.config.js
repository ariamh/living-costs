import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: '0.0.0.0', // WAJIB agar dapat diakses dari host
        port: 5173,
        strictPort: true,
        watch: {
        usePolling: true, // Docker membutuhkan polling
        },
    },
});
