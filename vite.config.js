import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/theme.css',
                'resources/js/marker.js',
                'resources/js/autofocus.js',
                'resources/css/gui.css',
                'resources/js/gui.js',
            ],
            refresh: [
                'app/Livewire/**',
                'app/Utils/**',
            ],
            applyComplexClasses: true,
        }),
    ],
});
