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
                    <button id="installPwaButton" class="btn btn-secondary pwa-install-button" type="button" hidden>
                        Instalar aplicación
                    </button>
                    @if (! empty($navOperationalUser))
                        @php
                            $navImage = $navOperationalUser->image;
                            $navImagePath = $navImage ? public_path('UserImages/' . $navImage) : null;
                            $navAvatar = $navImagePath && file_exists($navImagePath)
                                ? asset('UserImages/' . $navImage)
                                : asset('common/favicon.png');
                        @endphp
                        <div class="profile-menu">
                            <button class="profile-menu-toggle" type="button" aria-expanded="false"
                                aria-controls="profileMenuDropdown">
                                <img src="{{ $navAvatar }}" alt="Foto de {{ $navOperationalUser->name }}">
                                <span>{{ $navOperationalUser->name }}</span>
                            </button>
                            <div id="profileMenuDropdown" class="profile-menu-dropdown">
                                <a href="{{ route('user.profile') }}">Perfil</a>
                                <form method="post" action="{{ route('user.logout') }}">
                                    @csrf
                                    <button type="submit">Cerrar sesión</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('user.login') }}" class="btn btn-primary">Ingresar</a>
                    @endif
                </nav>
            </div>
        </div>
    </header>

    <aside id="iosInstallHint" class="pwa-install-hint" hidden>
        <div>
            <strong>Instalar Genius Kaan</strong>
            <span>En iPhone o iPad: abre Safari, toca Compartir y selecciona Agregar a pantalla de inicio.</span>
        </div>
        <button id="dismissIosInstallHint" type="button" aria-label="Cerrar aviso">Cerrar</button>
    </aside>

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
