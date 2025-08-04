import '../css/app.css'

import { createInertiaApp, router } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import type { DefineComponent } from 'vue'
import { createApp, h } from 'vue'
import { initializeTheme } from './composables/useAppearance'
import {
    browserSupportsWebAuthn,
    startAuthentication,
    startRegistration
} from '@simplewebauthn/browser'

window.browserSupportsWebAuthn = browserSupportsWebAuthn
window.startAuthentication = startAuthentication
window.startRegistration = startRegistration

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup ({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el)
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

    const parsed = JSON.parse((event as any).detail.response as string)

    const urls = parsed?.props?.prefetch
    if (!Array.isArray(urls) || urls.length === 0) return
    const unique = Array.from(
        new Set(urls.filter((u): u is string => typeof u === 'string' && u.length > 0))
    )

    for (const url of unique) {
        fetch(url, { method: 'GET', credentials: 'same-origin' }).catch(() => {})
    }
})
