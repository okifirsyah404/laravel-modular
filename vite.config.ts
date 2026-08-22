import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import path from 'path';
import { defineConfig } from 'vite';
import collectModuleAssetsPaths from './vite-module-loader';

async function getConfig() {
    const paths = ['resources/css/app.css', 'resources/js/app.tsx'];
    const inputPaths = await collectModuleAssetsPaths(paths, 'Modules');

    return defineConfig({
        plugins: [
            laravel({
                input: inputPaths,
                refresh: true,
                fonts: [
                    bunny('Instrument Sans', {
                        weights: [400, 500, 600],
                    }),
                ],
            }),
            inertia(),
            react({}),
            tailwindcss(),
            wayfinder({
                routes: true,
                actions: true,
                formVariants: true,
            }),
        ],
        server: {
            fs: {
                allow: [
                    path.resolve(__dirname),
                    path.resolve(__dirname, 'Modules'),
                ],
            },
            watch: {
                ignored: [
                    '**/.agents/**',
                    '**/.claude/**',
                    '**/.cursor/**',
                    '**/.junie/**',
                    '**/vendor/**',
                ],
            },
        },
    });
}

export default getConfig();
