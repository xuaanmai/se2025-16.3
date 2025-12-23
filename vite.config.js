import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import path from 'path';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js'
            ],
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
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'), // Dùng path.resolve để chính xác hơn
            'frappe-gantt/dist/frappe-gantt.css': path.resolve(__dirname, 'node_modules/frappe-gantt/dist/frappe-gantt.css'),
        },
    },
    // BỔ SUNG KHỐI NÀY ĐỂ CHẠY TRÊN DOCKER/GCP
    server: {
        host: '0.0.0.0', // Cho phép truy cập từ ngoài container
        port: 5173,      // Cổng mặc định của Vite
        hmr: {
            host: '35.202.29.6' // IP của máy chủ để trình duyệt kết nối đúng chỗ
        },
        watch: {
            usePolling: true // Cần thiết để nhận diện thay đổi file trong Docker
        }
    },
});