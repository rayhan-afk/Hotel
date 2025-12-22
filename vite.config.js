import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path';
import viteCompression from 'vite-plugin-compression';

export default defineConfig({
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "./src"),
        },
    },
    plugins: [
        laravel({
            input: [
                "resources/sass/app.scss",
                "resources/js/app.js",
                "resources/js/pages/POS.js",
                "resources/js/pages/recipe.js",
            ],
            refresh: true,
        }),
        viteCompression(),
    ],
});
