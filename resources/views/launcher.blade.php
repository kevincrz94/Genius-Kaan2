@extends('layouts.public')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <span class="eyebrow">Configurar entrenamiento</span>
            <h1>
                Prepara una sesión cognitiva con
                <span class="highlight">parámetros claros antes de lanzar el juego</span>.
            </h1>
            <p class="lead">
                Este flujo permite capturar participante, objetivo y credenciales antes de entrar al launcher React de
                Cognifit. Así el acceso público queda separado del panel administrativo.
            </p>

            <div class="badge-row">
                <span class="soft-chip">Requiere user token válido</span>
                <span class="soft-chip">Selección de juego editable</span>
                <span class="soft-chip">Listo para conectar resultados</span>
            </div>
        </div>

        <div class="panel hero-board">
            <article class="card session-card">
                <span class="eyebrow">Antes de iniciar</span>
                <h3>Checklist mínimo para una sesión útil</h3>
                <div class="checklist">
                    <div class="check-item">
                        <strong>Token correcto</strong>
                        <span>Usa el user token generado por Cognifit para evitar errores de lanzamiento.</span>
                    </div>
                    <div class="check-item">
                        <strong>Objetivo concreto</strong>
                        <span>Ejemplo: foco sostenido, memoria funcional o velocidad de procesamiento.</span>
                    </div>
                    <div class="check-item">
                        <strong>Entorno estable</strong>
                        <span>Sesiones breves, sin interrupciones y con revisión posterior del resultado.</span>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="dashboard">
            <article class="panel card">
                <span class="eyebrow">Formulario de arranque</span>
                <h2>Completa la sesión y abre el launcher.</h2>
                <p class="section-copy">
                    El formulario usa GET para que puedas compartir enlaces de prueba o volver a abrir una misma
                    configuración rápidamente.
                </p>

                <form action="{{ route('start.game') }}" method="GET">
                    <div class="form-grid">
                        <div class="field">
                            <label for="participant">Participante</label>
                            <input id="participant" class="input" type="text" name="participant"
                                value="{{ $sessionDefaults['participant'] }}" required>
                        </div>

                        <div class="field">
                            <label for="locale">Idioma</label>
                            <select id="locale" name="locale" class="input">
                                <option value="es" @selected($sessionDefaults['locale'] === 'es')>es</option>
                                <option value="en" @selected($sessionDefaults['locale'] === 'en')>en</option>
                            </select>
                        </div>

                        <div class="field field-full">
                            <label for="goal">Meta de la sesión</label>
                            <input id="goal" class="input" type="text" name="goal"
                                value="{{ $sessionDefaults['goal'] }}" required>
                        </div>

                        <div class="field">
                            <label for="game_key">Juego Cognifit</label>
                            <select id="game_key" name="game_key" class="input" required>
                                @foreach ($availableGames as $game)
                                    <option value="{{ $game['key'] }}" @selected($sessionDefaults['game_key'] === $game['key'])>
                                        {{ $game['title'] }} ({{ $game['key'] }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="field">
                            <label for="user_token">User token</label>
                            <input id="user_token" class="input" type="text" name="user_token"
                                value="{{ $sessionDefaults['user_token'] }}" placeholder="Pega aquí el token" required>
                        </div>
                    </div>

                    <div class="cta-row">
                        <button type="submit" class="btn btn-primary">Abrir entrenamiento</button>
                        <a href="{{ route('user.login') }}" class="btn btn-secondary">Ir al acceso institucional</a>
                    </div>
                </form>
            </article>

            <article class="panel card">
                <span class="eyebrow">Juegos sugeridos</span>
                <h2>Biblioteca inicial para el MVP.</h2>
                <p class="section-copy">
                    La selección puede crecer después con catálogos por habilidad, edad o intensidad de entrenamiento.
                </p>

                @foreach ($availableGames as $game)
                    <div class="game-option">
                        <div>
                            <strong>{{ $game['title'] }}</strong>
                            <p class="helper-copy">{{ $game['focus'] }}</p>
                        </div>
                        <span class="key-copy">{{ $game['key'] }}</span>
                    </div>
                @endforeach

                <div class="hero-note">
                    Si quieres cerrar el flujo completo, el siguiente paso es registrar sesiones, guardar históricos y
                    mostrar reportes por usuario en la portada o en el panel.
                </div>
            </article>
        </div>
    </section>
@endsection
