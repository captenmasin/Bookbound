import '../css/app.css'

import { createInertiaApp, router } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import type { DefineComponent } from 'vue'
import { createApp, h, createApp as createVueApp } from 'vue'
import { initializeTheme } from './composables/useAppearance'
import {
    browserSupportsWebAuthn,
    startAuthentication,
    startRegistration
} from '@simplewebauthn/browser'
import SwUpdateModal from './components/SwUpdateModal.vue'

window.browserSupportsWebAuthn = browserSupportsWebAuthn
window.startAuthentication = startAuthentication
window.startRegistration = startRegistration

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

let swUpdateModal: any = null

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup ({ el, App, props, plugin }) {
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
        vueApp.mount(el)

        // Mount the modal as a separate Vue app
        const modalDiv = document.createElement('div')
        document.body.appendChild(modalDiv)
        swUpdateModal = createVueApp(SwUpdateModal).mount(modalDiv)
    },
    progress: {
        color: '#913608'
    }
})

// This will set light / dark mode on page load...
initializeTheme()

router.on('prefetched', (event) => {
    if (!event?.detail?.response) {
        return
    }

    const parsed = JSON.parse(event.detail.response)
    const urls = parsed?.props?.prefetch
    if (!Array.isArray(urls) || urls.length === 0) return
    const unique = Array.from(
        new Set(urls.filter((u): u is string => typeof u === 'string' && u.length > 0))
    )

    for (const url of unique) {
        fetch(url, { method: 'GET', credentials: 'same-origin' }).catch(() => {})
    }
})

// Register service worker and show update modal
// if ('serviceWorker' in navigator) {
//     window.addEventListener('load', () => {
//         navigator.serviceWorker.register('/service-worker.js').then(registration => {
//             // Listen for updates
//             registration.addEventListener('updatefound', () => {
//                 const newWorker = registration.installing
//                 if (newWorker) {
//                     newWorker.addEventListener('statechange', () => {
//                         if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
//                             // Show custom modal
//                             swUpdateModal?.show(newWorker)
//                         }
//                     })
//                 }
//             })
//
//             // Also handle already waiting SW (e.g. on page reload)
//             if (registration.waiting) {
//                 swUpdateModal?.show(registration.waiting)
//             }
//         }).catch(() => {})
//     })
// }
