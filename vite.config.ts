import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/js/index.css',
                'resources/js/main.tsx',
                'resources/js/pdf-preview.tsx'
            ],
            refresh: true,
        }),
    ],
    esbuild: {
        jsx: 'automatic',
        jsxImportSource: 'react',
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: {
            host: 'localhost',
        },
    },
});
