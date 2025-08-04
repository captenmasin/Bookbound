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
@endif
