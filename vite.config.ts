import vue from '@vitejs/plugin-vue'
import inertia from '@inertiajs/vite'
import laravel from 'laravel-vite-plugin'
import path from 'path'
// @ts-ignore
import tailwindcss from '@tailwindcss/vite'
import run from 'vite-plugin-run'
import { defineConfig } from 'vite'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            refresh: true
        }),
        inertia(),
        tailwindcss(),
        run([
            {
                name: 'generate routes',
                run: ['php', 'artisan', 'ziggy:generate'],
                pattern: ['routes/*.php']
            },
            {
                name: 'generate enums for frontend',
                run: ['php', 'artisan', 'frontend:enums'],
                pattern: ['app/Enums/*.php']
            }
        ]),
        vue()
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            '~': path.resolve('./resources')
        }
    },
})
