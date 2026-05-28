<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle ?? 'Genius Kaan' }}</title>
    <link rel="icon" href="{{ asset('common/favicon.png') }}">
    @include('partials.pwa')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/public.css') }}">
    @stack('styles')
</head>

<body>
    <header class="site-header">
        <div class="container">
            <div class="header-panel">
                <a href="{{ route('home') }}" class="brand">
                    <img class="brand-mark" src="{{ asset('common/light-logo.png') }}" alt="Genius Kaan">
                    <span class="brand-copy">
                        Genius Kaan
                        <small>Aptitud cognitiva operativa</small>
                    </span>
                </a>

                <nav class="nav-actions">
                    <a href="{{ route('home') }}" class="btn btn-secondary">Inicio</a>
                    <a href="{{ route('user.login') }}" class="btn btn-primary">Ingresar</a>
                </nav>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            Genius Kaan organiza evaluación, entrenamiento y seguimiento cognitivo para fuerzas de seguridad pública.
        </div>
    </footer>
    @stack('scripts')
</body>

</html>
