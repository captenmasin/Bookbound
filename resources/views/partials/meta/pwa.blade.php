@if(config('pwa.enabled'))
    @include('partials.meta.icons', ['buildId' => $buildId ?? ''])

    <meta name="theme-color" content="{{ config('site.colours.primary') }}">

    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="{{ config('app.name') }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="{{ config('pwa.manifest.status_bar') }}">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
    <meta name="msapplication-TileImage" content="/images/pwa/icons/512.png">

    <link rel="manifest" href="{{ url('manifest.json') . '?v=' . Vite::manifestHash('build') }}"
          crossorigin="use-credentials">

    <script type="module">
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                // eslint-disable-next-line sonarjs/cognitive-complexity
                navigator.serviceWorker.register('/service-worker.js').then(registration => {
                    // Listen for updates
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing
                        if (newWorker) {
                            newWorker.addEventListener('statechange', () => {
                                // eslint-disable-next-line sonarjs/no-collapsible-if
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New SW is waiting, show popup
                                    if (confirm('A new version is available. Refresh to update?')) {
                                        newWorker.postMessage({ type: 'SKIP_WAITING' })
                                        window.location.reload()
                                    }
                                }
                            })
                        }
                    })

                    // Also handle already waiting SW (e.g. on page reload)
                    if (registration.waiting) {
                        if (confirm('A new version of {{ config('app.name') }} is available. Refresh to update?')) {
                            registration.waiting.postMessage({ type: 'SKIP_WAITING' })
                            window.location.reload()
                        }
                    }
                }).catch(() => {})
            })
        }
    </script>
@endif
