import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin'
import path from 'path'
// @ts-ignore
import tailwindcss from '@tailwindcss/vite'
import run from 'vite-plugin-run'
import { defineConfig } from 'vite'
import { nativephpHotFile, nativephpMobile } from './vendor/nativephp/mobile/resources/js/vite-plugin.js'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.ts'],
            ssr: 'resources/js/ssr.ts',
            refresh: true,
            hotFile: nativephpHotFile()
        }),
        tailwindcss(),
        nativephpMobile(),
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
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false
                }
            }
        })
    ],
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './resources/js'),
            '~': path.resolve('./resources'),
            '@nativephp/mobile': path.resolve(__dirname, './vendor/nativephp/mobile/resources/dist/native.js')
        }
    },
    build: {
        target: 'esnext', // Assumes modern browser support
        minify: 'esbuild', // Fastest minifier
        cssCodeSplit: true,
        emptyOutDir: true
    },
    optimizeDeps: {
        include: ['vue', '@inertiajs/inertia', '@inertiajs/inertia-vue3'],
        esbuildOptions: {
            target: 'es2020'
        }
    },
    server: {
        fs: {
            strict: true
        }
    }
})
