<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <link rel="icon" href="{{ asset('common/favicon.png') }}">
    @include('partials.pwa')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700;800&family=Space+Grotesk:wght@500;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://js.cognifit.com/v1/wigetStyles.css">
    <style>
        :root {
            --ink: #e2e8f0;
            --muted: #94a3b8;
            --panel: rgba(15, 23, 42, 0.82);
            --line: rgba(148, 163, 184, 0.18);
            --accent: #0d6efd;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Manrope', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(13, 110, 253, 0.2), transparent 30%),
                linear-gradient(180deg, #0f172a 0%, #111827 100%);
        }

        .launcher-page {
            min-height: 100vh;
            padding: 1.5rem;
        }

        .launcher-header,
        .launcher-grid {
            width: min(1320px, 100%);
            margin: 0 auto;
        }

        .launcher-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .kicker {
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: #93c5fd;
        }

        h1,
        h2 {
            margin: 0;
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: -0.04em;
        }

        h1 {
            margin-top: 0.45rem;
            font-size: clamp(2rem, 4vw, 3.3rem);
            line-height: 1.05;
        }

        p {
            color: var(--muted);
            line-height: 1.75;
        }

        .launcher-grid {
            display: grid;
            grid-template-columns: 340px minmax(0, 1fr);
            gap: 1rem;
        }

        .panel {
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--panel);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.25);
        }

        .info-panel {
            padding: 1.2rem;
        }

        .meta {
            display: grid;
            gap: 0.8rem;
            margin-top: 1rem;
        }

        .meta-item {
            padding: 0.9rem;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.04);
        }

        .meta-item span {
            display: block;
            color: var(--muted);
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .meta-item strong {
            display: block;
            margin-top: 0.35rem;
            word-break: break-word;
        }

        .btn-row {
            display: grid;
            gap: 0.75rem;
            margin-top: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 3rem;
            padding: 0 1rem;
            border: 1px solid var(--line);
            border-radius: 8px;
            color: white;
            background: rgba(255, 255, 255, 0.06);
            font: inherit;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-primary {
            border-color: rgba(13, 110, 253, 0.6);
            background: #0d6efd;
        }

        .game-panel {
            padding: 1rem;
        }

        #cognifit-container {
            min-height: 74vh;
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            background: #0b1220;
        }

        .status {
            margin-bottom: 1rem;
            padding: 0.9rem 1rem;
            border: 1px solid var(--line);
            border-radius: 10px;
            color: var(--muted);
            background: rgba(255, 255, 255, 0.04);
        }

        @media (max-width: 980px) {
            .launcher-grid,
            .launcher-header {
                grid-template-columns: 1fr;
                flex-direction: column;
            }
        }
    </style>
    <script src="https://js.cognifit.com/v1/gameWidget.js"></script>
</head>

<body>
    @php
        $gameKey = $launchConfig['gameKey'] ?? '';
        $participant = $launchConfig['participant'] ?? 'Elemento';
        $goal = $launchConfig['goal'] ?? 'Entrenamiento cognitivo';
        $userToken = $launchConfig['userToken'] ?? '';
        $locale = $launchConfig['locale'] ?? 'es';
    @endphp

    <div class="launcher-page">
        <header class="launcher-header">
            <div>
                <span class="kicker">Genius Kaan</span>
                <h1>{{ $participant }}</h1>
                <p>{{ $goal }}</p>
            </div>
        </header>

        <main class="launcher-grid">
            <aside class="panel info-panel">
                <span class="kicker">Sesión</span>
                <h2>Contexto del juego</h2>

                <div class="meta">
                    <div class="meta-item">
                        <span>Juego</span>
                        <strong>{{ $gameKey ?: 'Pendiente' }}</strong>
                    </div>
                    <div class="meta-item">
                        <span>Token</span>
                        <strong>{{ $userToken ? substr($userToken, 0, 6).'...'.substr($userToken, -4) : 'Sin token' }}</strong>
                    </div>
                    <div class="meta-item">
                        <span>Idioma</span>
                        <strong>{{ strtoupper($locale) }}</strong>
                    </div>
                </div>

                <div class="btn-row">
                    <button id="start-game-button" class="btn btn-primary" type="button">Iniciar juego</button>
                    <a class="btn" href="{{ route('user.games') }}">Volver a juegos</a>
                </div>
            </aside>

            <section class="panel game-panel">
                <div id="game-status" class="status">Preparando juego Cognifit.</div>
                <div id="cognifit-container"></div>
            </section>
        </main>
    </div>

    <noscript>
        <div style="font-family: Manrope, sans-serif; color: white; padding: 2rem;">
            Activa JavaScript para lanzar el entrenamiento cognitivo.
        </div>
    </noscript>

    <script>
        const gameKey = @json($gameKey);
        const statusBox = document.getElementById('game-status');
        const button = document.getElementById('start-game-button');

        function startCognifitGame() {
            if (!gameKey) {
                statusBox.textContent = 'No se recibió la clave del juego.';
                return;
            }

            if (!window.HTML5JS || typeof window.HTML5JS.loadGame !== 'function') {
                statusBox.textContent = 'No se pudo cargar el widget de Cognifit.';
                return;
            }

            statusBox.textContent = 'Cargando juego ' + gameKey + '.';
            window.HTML5JS.loadGame(gameKey, 'cognifit-container');
        }

        button.addEventListener('click', startCognifitGame);
        window.addEventListener('load', startCognifitGame);
    </script>
</body>

</html>
