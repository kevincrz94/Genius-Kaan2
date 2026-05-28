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
    <style>
        :root {
            --ink: #00254c;
            --muted: #607086;
            --surface: rgba(255, 255, 255, 0.9);
            --surface-strong: rgba(255, 255, 255, 0.98);
            --line: rgba(0, 37, 76, 0.12);
            --accent: #0d6efd;
            --accent-deep: #00254c;
            --teal: #145da0;
            --gold: #c7a34b;
            --shadow: 0 24px 70px rgba(0, 37, 76, 0.14);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            color: var(--ink);
            background:
                radial-gradient(circle at top left, rgba(13, 110, 253, 0.12), transparent 28%),
                radial-gradient(circle at top right, rgba(0, 37, 76, 0.12), transparent 24%),
                linear-gradient(180deg, #f4f7fb 0%, #e8eef6 100%);
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(0, 37, 76, 0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(0, 37, 76, 0.04) 1px, transparent 1px);
            background-size: 34px 34px;
            pointer-events: none;
            opacity: 0.45;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .container {
            position: relative;
            z-index: 1;
            width: min(1160px, calc(100% - 2rem));
            margin: 0 auto;
        }

        .site-header {
            padding: 1.2rem 0 0;
        }

        .header-panel {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1rem 1.2rem;
            border: 1px solid var(--line);
            border-radius: 12px;
            background: #ffffff;
            box-shadow: var(--shadow);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 700;
            letter-spacing: -0.04em;
        }

        .brand-mark {
            width: 5.2rem;
            height: 3rem;
            display: block;
            object-fit: contain;
            padding: 0.35rem 0.55rem;
            border-radius: 8px;
            background: var(--ink);
            box-shadow: 0 10px 24px rgba(0, 37, 76, 0.18);
        }

        .brand-copy small {
            display: block;
            color: var(--muted);
            font-family: 'Manrope', sans-serif;
            font-size: 0.78rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .nav-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 3rem;
            padding: 0 1.2rem;
            border-radius: 6px;
            border: 1px solid transparent;
            font-weight: 700;
            transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .btn-primary {
            color: white;
            background: var(--ink);
            box-shadow: 0 16px 32px rgba(0, 37, 76, 0.22);
        }

        .btn-secondary {
            color: var(--ink);
            background: rgba(0, 37, 76, 0.04);
            border-color: rgba(0, 37, 76, 0.12);
        }

        main {
            padding-bottom: 4rem;
        }

        .hero,
        .dashboard {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            gap: 1rem;
            margin-top: 1rem;
        }

        .panel,
        .card {
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }

        .hero-copy {
            padding: clamp(2rem, 4vw, 3.4rem);
        }

        .hero-board {
            padding: 1.3rem;
            display: grid;
            gap: 1rem;
            background:
                radial-gradient(circle at top right, rgba(13, 110, 253, 0.12), transparent 25%),
                linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(255, 255, 255, 0.86) 100%);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--ink);
        }

        .eyebrow::before {
            content: '';
            width: 0.68rem;
            height: 0.68rem;
            border-radius: 999px;
            background: var(--gold);
            box-shadow: 0 0 0 8px rgba(199, 163, 75, 0.14);
        }

        h1,
        h2,
        h3 {
            margin: 0;
            font-family: 'Space Grotesk', sans-serif;
            letter-spacing: -0.04em;
        }

        .hero-copy h1 {
            margin-top: 1rem;
            font-size: clamp(2.6rem, 6vw, 4.8rem);
            line-height: 0.96;
        }

        .highlight {
            color: var(--accent-deep);
        }

        .lead,
        .section-copy,
        .card p,
        .line-item p {
            color: var(--muted);
            line-height: 1.8;
        }

        .lead {
            max-width: 46rem;
            margin: 1.2rem 0 0;
            font-size: 1.08rem;
        }

        .cta-row,
        .badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .cta-row {
            margin-top: 1.5rem;
        }

        .badge-row {
            margin-top: 1rem;
        }

        .soft-chip,
        .line-tag {
            display: inline-flex;
            align-items: center;
            min-height: 2.25rem;
            padding: 0 0.9rem;
            border-radius: 6px;
            background: rgba(0, 37, 76, 0.06);
            color: var(--ink);
            font-size: 0.9rem;
            font-weight: 700;
        }

        .metric-grid,
        .grid-3,
        .form-grid {
            display: grid;
            gap: 1rem;
        }

        .metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .metric-card,
        .feature-card,
        .timeline-card,
        .scoreboard-card,
        .cta-panel,
        .session-card {
            padding: 1.4rem;
        }

        .metric-card {
            min-height: 11rem;
            background: var(--surface-strong);
        }

        .metric-card span,
        .score-row span {
            color: var(--muted);
            font-size: 0.88rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .metric-card strong {
            display: block;
            margin: 0.8rem 0 0.45rem;
            font-size: clamp(1.9rem, 3vw, 2.5rem);
        }

        .section {
            margin-top: 3.4rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 1rem;
            margin-bottom: 1.2rem;
        }

        .section-header h2 {
            font-size: clamp(1.8rem, 3vw, 3rem);
            max-width: 34rem;
        }

        .section-copy {
            max-width: 34rem;
            margin: 0;
        }

        .accent-dot {
            width: 0.9rem;
            height: 0.9rem;
            margin-bottom: 1rem;
            border-radius: 999px;
            background: var(--card-accent, var(--accent));
            box-shadow: 0 0 0 10px rgba(20, 33, 61, 0.04);
        }

        .feature-card h3,
        .timeline-card h3 {
            margin-bottom: 0.7rem;
            font-size: 1.35rem;
        }

        .step-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            background: rgba(0, 37, 76, 0.08);
            color: var(--ink);
            font-weight: 800;
        }

        .stack-list {
            display: grid;
            gap: 0.9rem;
            margin-top: 1.4rem;
        }

        .line-item,
        .game-option {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            padding: 1rem 0;
            border-top: 1px solid rgba(0, 37, 76, 0.08);
        }

        .line-item:first-child,
        .game-option:first-child {
            border-top: 0;
            padding-top: 0;
        }

        .line-item strong,
        .game-option strong {
            display: block;
            margin-bottom: 0.35rem;
            font-size: 1.04rem;
        }

        .score-row + .score-row {
            margin-top: 1rem;
        }

        .score-row strong {
            display: block;
            margin-top: 0.3rem;
            font-size: 1.3rem;
        }

        .progress-bar {
            margin-top: 0.7rem;
            width: 100%;
            height: 0.7rem;
            border-radius: 999px;
            background: rgba(0, 37, 76, 0.08);
            overflow: hidden;
        }

        .progress-bar span {
            display: block;
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(135deg, var(--ink), var(--accent));
        }

        .hero-note {
            margin-top: 1.4rem;
            padding: 1rem 1.1rem;
            border-radius: 8px;
            background: rgba(0, 37, 76, 0.05);
            color: var(--muted);
            line-height: 1.7;
        }

        .checklist {
            display: grid;
            gap: 0.9rem;
            margin-top: 1rem;
        }

        .check-item {
            display: grid;
            gap: 0.2rem;
            padding: 0.9rem 1rem;
            border-radius: 8px;
            background: rgba(0, 37, 76, 0.05);
        }

        .check-item strong {
            font-size: 1rem;
        }

        .check-item span {
            color: var(--muted);
            line-height: 1.7;
        }

        .form-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            margin-top: 1.4rem;
        }

        .field {
            display: grid;
            gap: 0.55rem;
        }

        .field-full {
            grid-column: 1 / -1;
        }

        .field label {
            font-size: 0.92rem;
            font-weight: 700;
        }

        .input,
        select,
        textarea {
            width: 100%;
            min-height: 3.25rem;
            padding: 0.95rem 1rem;
            border: 1px solid rgba(0, 37, 76, 0.12);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.86);
            color: var(--ink);
            font: inherit;
        }

        .helper-copy,
        .key-copy {
            color: var(--muted);
            line-height: 1.7;
        }

        .key-copy {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 0.9rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--teal);
        }

        .footer {
            padding: 0 0 2.5rem;
            color: var(--muted);
            font-size: 0.95rem;
        }

        @media (max-width: 960px) {
            .hero,
            .dashboard,
            .grid-3,
            .section-header,
            .form-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                align-items: flex-start;
            }

            .header-panel {
                flex-direction: column;
                align-items: flex-start;
            }

            .metric-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .container {
                width: min(100% - 1rem, 1160px);
            }

            .hero-copy,
            .metric-card,
            .feature-card,
            .timeline-card,
            .scoreboard-card,
            .cta-panel,
            .session-card {
                padding: 1.2rem;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
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
