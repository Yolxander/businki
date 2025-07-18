import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
        },
    },
    define: {
        'process.env.NODE_ENV': JSON.stringify(process.env.NODE_ENV),
    },
    build: {
        rollupOptions: {
            onwarn(warning, warn) {
                // Ignore "use client" directive warnings from Radix UI
                if (warning.code === 'MODULE_LEVEL_DIRECTIVE' && warning.message.includes('use client')) {
                    return;
                }
                warn(warning);
            },
        },
    },
});
