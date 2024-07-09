import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/css/empresas.css',
                'resources/css/index.css',
                'resources/js/index.js',
                'resources/css/bootstrap.css',
                'resources/css/datatables.css',
                'resources/css/fullcalendar.css',
                'resources/css/atomic.css',
                'resources/js/jquery.js',
                'resources/js/bootstrap.bundle.js',
                'resources/js/fullcalendar.js',
                'resources/js/atomic.js'
            ],
            refresh: true,
        }),
    ],
});
