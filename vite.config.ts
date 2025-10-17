import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import path from 'path';

// https://vitejs.dev/config/
export default defineConfig({
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: false,
        hmr: {
            host: 'localhost',
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/js/index.css',
                'resources/js/main.tsx'
            ],
            refresh: true,
        }),
        react({
            jsxRuntime: 'automatic',
            babel: {
                plugins: [],
            },
        }),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
});
