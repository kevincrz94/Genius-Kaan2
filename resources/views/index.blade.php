<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <link rel="icon" href="{{ asset('common/favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap"
        rel="stylesheet">
</head>

<body style="margin: 0; background: #0f172a;">
    <div id="app" data-launch-config="{{ e(json_encode($launchConfig)) }}"></div>
    <noscript>
        <div
            style="font-family: Manrope, sans-serif; min-height: 100vh; display: grid; place-items: center; color: white; padding: 2rem;">
            Activa JavaScript para lanzar el entrenamiento cognitivo.
        </div>
    </noscript>

    @viteReactRefresh
    @vite('resources/js/app.jsx')
</body>

</html>
