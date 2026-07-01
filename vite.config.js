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
    server: {
        watch: {
            ignored: ['**/hsicontrol-ext/**']
        }
    },
    build: {
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
