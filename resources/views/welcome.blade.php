@extends('layouts.public')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <span class="eyebrow">Plataforma de desarrollo cognitivo</span>
            <h1>
                Entrenamiento cerebral con objetivos
                <span class="highlight">claros, medibles y humanos</span>.
            </h1>
            <p class="lead">
                Genius Kaan conecta evaluacion, entrenamiento y seguimiento para que cada sesion responda a una necesidad
                real. La experiencia esta pensada para familias, terapeutas, escuelas y equipos clinicos.
            </p>

            <div class="cta-row">
                <a href="{{ route('launcher') }}" class="btn btn-primary">Preparar una sesion</a>
                <a href="{{ route('admin.showLogin') }}" class="btn btn-secondary">Entrar al panel</a>
            </div>

            <div class="badge-row">
                <span class="soft-chip">Entrenamiento con Cognifit</span>
                <span class="soft-chip">Seguimiento por habilidades</span>
                <span class="soft-chip">Uso para ninos, adultos y adultos mayores</span>
            </div>
        </div>

        <div class="panel hero-board">
            <div class="metric-grid">
                @foreach ($signals as $signal)
                    <article class="metric-card">
                        <span>{{ $signal['label'] }}</span>
                        <strong>{{ $signal['value'] }}</strong>
                        <p>{{ $signal['description'] }}</p>
                    </article>
                @endforeach
            </div>

            <article class="card session-card">
                <span class="eyebrow">Ruta sugerida</span>
                <h3>De la evaluacion al cambio observable</h3>
                <p>
                    Define la meta, prepara la sesion, lanza el entrenamiento y revisa resultados desde un mismo flujo
                    operativo.
                </p>

                <div class="checklist">
                    <div class="check-item">
                        <strong>Evaluacion inicial</strong>
                        <span>Ubica fortalezas y brechas cognitivas antes de entrenar.</span>
                    </div>
                    <div class="check-item">
                        <strong>Plan diario</strong>
                        <span>Bloques breves con objetivos que si se pueden seguir.</span>
                    </div>
                    <div class="check-item">
                        <strong>Panel compartido</strong>
                        <span>Visualiza avances y consistencia con datos accionables.</span>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Que entrenamos</span>
                <h2>Un sistema para trabajar habilidades que impactan el dia a dia.</h2>
            </div>
            <p class="section-copy">
                El objetivo no es acumular juegos. El objetivo es construir rutinas que mejoren conducta, autonomia y
                desempeno funcional.
            </p>
        </div>

        <div class="grid-3">
            @foreach ($pillars as $pillar)
                <article class="card feature-card" style="--card-accent: {{ $pillar['accent'] }}">
                    <div class="accent-dot"></div>
                    <h3>{{ $pillar['title'] }}</h3>
                    <p>{{ $pillar['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Metodo de trabajo</span>
                <h2>Una ruta simple para ordenar el desarrollo cognitivo.</h2>
            </div>
            <p class="section-copy">
                El MVP queda listo para exponer la propuesta publica y para iniciar sesiones sin tocar el flujo de admin
                que ya existe.
            </p>
        </div>

        <div class="grid-3">
            @foreach ($journey as $item)
                <article class="card timeline-card">
                    <span class="step-badge">{{ $item['step'] }}</span>
                    <h3>{{ $item['title'] }}</h3>
                    <p>{{ $item['description'] }}</p>
                </article>
            @endforeach
        </div>
    </section>

    <section class="section">
        <div class="dashboard">
            <article class="panel card">
                <span class="eyebrow">Programas</span>
                <h2>La misma plataforma puede adaptarse a distintos ciclos de vida.</h2>
                <p class="section-copy">
                    Cada audiencia requiere tono, duracion y objetivos distintos. La aplicacion queda preparada para
                    crecer sobre esa base.
                </p>

                <div class="stack-list">
                    @foreach ($audiences as $audience)
                        <div class="line-item">
                            <div>
                                <strong>{{ $audience['title'] }}</strong>
                                <p>{{ $audience['description'] }}</p>
                            </div>
                            <span class="line-tag">Activo</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="panel card scoreboard-card">
                <span class="eyebrow">Vista de progreso</span>
                <h2>Indicadores listos para seguimiento operativo.</h2>

                <div class="score-row">
                    <div>
                        <span>Atencion sostenida</span>
                        <strong>84%</strong>
                    </div>
                    <div class="progress-bar"><span style="width: 84%"></span></div>
                </div>

                <div class="score-row">
                    <div>
                        <span>Memoria funcional</span>
                        <strong>72%</strong>
                    </div>
                    <div class="progress-bar"><span style="width: 72%"></span></div>
                </div>

                <div class="score-row">
                    <div>
                        <span>Consistencia semanal</span>
                        <strong>4/5</strong>
                    </div>
                    <div class="progress-bar"><span style="width: 80%"></span></div>
                </div>

                <div class="hero-note">
                    El siguiente paso natural es conectar esta portada con registros de usuarios, recomendaciones
                    automaticas y reportes historicos.
                </div>
            </article>
        </div>
    </section>

    <section class="section">
        <article class="panel card cta-panel">
            <span class="eyebrow">Siguiente accion</span>
            <h2>Arranca una sesion o entra al panel para gestionar usuarios y juegos.</h2>
            <p class="section-copy">
                La base publica y el launcher ya quedan separados del panel administrativo, lo que facilita iterar el
                producto sin bloquear la operacion interna.
            </p>

            <div class="cta-row">
                <a href="{{ route('launcher') }}" class="btn btn-primary">Configurar entrenamiento</a>
                <a href="{{ route('admin.showLogin') }}" class="btn btn-secondary">Abrir administracion</a>
            </div>
        </article>
    </section>
@endsection
