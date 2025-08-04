// This is the "Offline page" service worker

importScripts('https://storage.googleapis.com/workbox-cdn/releases/5.1.2/workbox-sw.js')

const CACHE = 'pwa-cache'

const offlineFallbackPage = 'offline.html'

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting()
    }
})

self.addEventListener('install', async (event) => {
    event.waitUntil(
        (async () => {
            const cache = await caches.open(CACHE)
            try {
                await cache.addAll([offlineFallbackPage])
            } catch (e) {
                console.log(e)
                console.error('Failed to cache offline page:', e)
            }
        })()
    )
})

if (workbox.navigationPreload.isSupported()) {
    workbox.navigationPreload.enable()
}

self.addEventListener('fetch', (event) => {
    if (event.request.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                const preloadResp = await event.preloadResponse

                if (preloadResp) {
                    return preloadResp
                }

                return await fetch(event.request)
            } catch (error) {
                const cache = await caches.open(CACHE)
                return await cache.match(offlineFallbackPage)
            }
        })())
    }
})
