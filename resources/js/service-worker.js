/* global workbox */
// Offline-capable Service Worker using Workbox

importScripts('https://storage.googleapis.com/workbox-cdn/releases/5.1.2/workbox-sw.js')

const APP_PREFIX = '__APP_NAME_PLACEHOLDER__'
const VERSION = 'v__VERSION_PLACEHOLDER__'
const OFFLINE_URL = 'offline.html'

// --- Cache naming
workbox.core.setCacheNameDetails({
    prefix: APP_PREFIX,
    suffix: VERSION,
    precache: 'precache',
    runtime: 'runtime'
})

// --- Skip waiting on message (already in your code)
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') self.skipWaiting()
})

// --- Install: precache offline page (revision ensures update)
workbox.precaching.precacheAndRoute([
    { url: OFFLINE_URL, revision: VERSION }
])

// Ensure new SW takes control quickly on first load after install
self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting())
})

// --- Activate: clean old caches & claim clients
self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        // (Workbox handles its own naming; this just claims clients)
        await self.clients.claim()
    })())
})

// --- Navigation preload (good!)
if (workbox.navigationPreload.isSupported()) {
    workbox.navigationPreload.enable()
}

// If all strategies fail (e.g., totally offline), serve offline.html for navigations
workbox.routing.setCatchHandler(async ({ event }) => {
    if (event.request.mode === 'navigate') {
        return caches.match(OFFLINE_URL, { ignoreSearch: true })
    }
    return Response.error()
})

/**
 * Runtime caching
 */

// CSS & JS: Stale-While-Revalidate (fast, then quietly update)
workbox.routing.registerRoute(
    ({ request }) => request.destination === 'script' || request.destination === 'style',
    new workbox.strategies.StaleWhileRevalidate({
        cacheName: `${APP_PREFIX}-${VERSION}-static`,
        plugins: [
            new workbox.expiration.ExpirationPlugin({
                maxEntries: 80,
                maxAgeSeconds: 30 * 24 * 60 * 60, // 30 days
                purgeOnQuotaError: true
            })
        ]
    })
)

// Images: Cache-First with limits
workbox.routing.registerRoute(
    ({ request }) => request.destination === 'image',
    new workbox.strategies.CacheFirst({
        cacheName: `${APP_PREFIX}-${VERSION}-images`,
        plugins: [
            new workbox.cacheableResponse.CacheableResponsePlugin({ statuses: [0, 200] }),
            new workbox.expiration.ExpirationPlugin({
                maxEntries: 150,
                maxAgeSeconds: 30 * 24 * 60 * 60,
                purgeOnQuotaError: true
            })
        ]
    })
)

// Fonts: Cache-First (often immutable)
workbox.routing.registerRoute(
    ({ request }) => request.destination === 'font',
    new workbox.strategies.CacheFirst({
        cacheName: `${APP_PREFIX}-${VERSION}-fonts`,
        plugins: [
            new workbox.cacheableResponse.CacheableResponsePlugin({ statuses: [0, 200] }),
            new workbox.expiration.ExpirationPlugin({
                maxEntries: 30,
                maxAgeSeconds: 365 * 24 * 60 * 60,
                purgeOnQuotaError: true
            })
        ]
    })
)
