import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            detectTls: false,
            refresh: true,
        }),
    ],
    build: {
        // Le decimos a Vite: "No te asustes a menos que un archivo supere los 1000 KB"
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('node_modules/vis-network')) {
                        return 'vis-vendor';
                    }
                }
            }
        }
    }
});