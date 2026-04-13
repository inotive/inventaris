import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    // REMOVED: base: '/public/' - Laravel Vite plugin already handles this
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    optimizeDeps: {
        include: [
            '@chenfengyuan/vue-qrcode',
            'leaflet',
            '@vue-leaflet/vue-leaflet'
        ],
        exclude: ['entities'],
        esbuildOptions: {
            // Workaround for entities package
            mainFields: ['module', 'main'],
        },
        force: true, // force re-bundle on server start
    },
    build: {
        commonjsOptions: {
            include: [/node_modules/],
            transformMixedEsModules: true,
        },
    },
    define: {
        global: 'globalThis',
    },
    assetsInclude: ['**/*.png', '**/*.jpg', '**/*.jpeg', '**/*.gif', '**/*.svg'],
});
