@extends('layouts.public')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <span class="eyebrow">Seguridad publica</span>
            <h1>
                Aptitud cognitiva operativa para
                <span class="highlight">elementos en servicio</span>.
            </h1>
            <p class="lead">
                Genius Kaan integra evaluacion, entrenamiento y seguimiento cognitivo para corporaciones de seguridad
                publica. Permite medir atencion, reaccion, memoria de trabajo y toma de decisiones por elemento, unidad,
                grupo operativo y categoria.
            </p>

            <div class="cta-row">
                <a href="{{ route('admin.showLogin') }}" class="btn btn-primary">Iniciar sesion</a>
            </div>

            <div class="badge-row">
                <span class="soft-chip">Elementos</span>
                <span class="soft-chip">Unidades</span>
                <span class="soft-chip">Grupos operativos</span>
                <span class="soft-chip">Alertas de refuerzo</span>
            </div>
        </div>

        <div class="panel hero-board">
            <div class="metric-grid">
                <article class="metric-card">
                    <span>Indice operativo</span>
                    <strong>0-100</strong>
                    <p>Lectura consolidada de desempeno cognitivo por elemento y unidad.</p>
                </article>
                <article class="metric-card">
                    <span>Categorias</span>
                    <strong>8</strong>
                    <p>Atencion, reaccion, memoria, control inhibitorio y toma de decisiones.</p>
                </article>
                <article class="metric-card">
                    <span>Seguimiento</span>
                    <strong>24/7</strong>
                    <p>Panel disponible para mandos, coordinadores y responsables de capacitacion.</p>
                </article>
                <article class="metric-card">
                    <span>Reportes</span>
                    <strong>PDF</strong>
                    <p>Informes individuales y comparativos para decisiones institucionales.</p>
                </article>
            </div>

            <article class="card session-card">
                <span class="eyebrow">Ruta operativa</span>
                <h3>Evaluar, entrenar, comparar y actuar.</h3>
                <p>
                    La plataforma convierte sesiones cognitivas en indicadores accionables para supervision,
                    capacitacion y seguimiento por mando.
                </p>

                <div class="checklist">
                    <div class="check-item">
                        <strong>Vision institucional</strong>
                        <span>Indicadores organizados para supervisores, mandos y responsables de capacitacion.</span>
                    </div>
                    <div class="check-item">
                        <strong>Medicion cognitiva</strong>
                        <span>Captura o sincroniza puntajes por categoria operativa.</span>
                    </div>
                    <div class="check-item">
                        <strong>Alertas de refuerzo</strong>
                        <span>Identifica elementos o grupos que requieren seguimiento.</span>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Capacidades criticas</span>
                <h2>Categorias cognitivas alineadas al desempeno operativo.</h2>
            </div>
            <p class="section-copy">
                El objetivo es traducir el entrenamiento cognitivo en criterios utiles para servicio, fatiga, reaccion,
                toma de decisiones y consistencia bajo carga mental.
            </p>
        </div>

        <div class="grid-3">
            <article class="card feature-card" style="--card-accent: #00254c">
                <div class="accent-dot"></div>
                <h3>Atencion sostenida</h3>
                <p>Capacidad para mantener foco durante patrullaje, monitoreo, vigilancia o tareas prolongadas.</p>
            </article>
            <article class="card feature-card" style="--card-accent: #0d6efd">
                <div class="accent-dot"></div>
                <h3>Tiempo de reaccion</h3>
                <p>Velocidad de respuesta ante estimulos relevantes, cambios de contexto y senales de riesgo.</p>
            </article>
            <article class="card feature-card" style="--card-accent: #c7a34b">
                <div class="accent-dot"></div>
                <h3>Control inhibitorio</h3>
                <p>Regulacion de impulsos y respuesta proporcional en escenarios de presion operativa.</p>
            </article>
            <article class="card feature-card" style="--card-accent: #145da0">
                <div class="accent-dot"></div>
                <h3>Toma de decisiones</h3>
                <p>Evaluacion rapida de alternativas para actuar con criterio, prioridad y trazabilidad.</p>
            </article>
            <article class="card feature-card" style="--card-accent: #4b5f7a">
                <div class="accent-dot"></div>
                <h3>Memoria de trabajo</h3>
                <p>Retencion y uso de informacion tactica, instrucciones, ubicaciones y protocolos.</p>
            </article>
            <article class="card feature-card" style="--card-accent: #23395d">
                <div class="accent-dot"></div>
                <h3>Carga mental</h3>
                <p>Seguimiento de consistencia cognitiva cuando aumenta la demanda o la fatiga.</p>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Metodo institucional</span>
                <h2>De la evaluacion al reporte para mando.</h2>
            </div>
            <p class="section-copy">
                La operacion se organiza por unidad, grupo y categoria para que los datos puedan compararse sin perder
                trazabilidad individual.
            </p>
        </div>

        <div class="grid-3">
            <article class="card timeline-card">
                <span class="step-badge">01</span>
                <h3>Evaluar capacidades</h3>
                <p>Observa categorias cognitivas relacionadas con atencion, reaccion, memoria y decision.</p>
            </article>
            <article class="card timeline-card">
                <span class="step-badge">02</span>
                <h3>Medir desempeno</h3>
                <p>Ejecuta sesiones Cognifit o captura mediciones internas con puntajes normalizados.</p>
            </article>
            <article class="card timeline-card">
                <span class="step-badge">03</span>
                <h3>Tomar accion</h3>
                <p>Consulta alertas, refuerzo requerido, comparativos y reportes por elemento o unidad.</p>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="dashboard">
            <article class="panel card">
                <span class="eyebrow">Mando y supervision</span>
                <h2>Lectura por estructura operativa.</h2>
                <p class="section-copy">
                    El panel permite observar el desempeno cognitivo desde distintos niveles de decision, sin reducir
                    el seguimiento a una lista aislada de registros.
                </p>

                <div class="stack-list">
                    <div class="line-item">
                        <div>
                            <strong>Elemento</strong>
                            <p>Ficha individual con indice operativo, categorias, historial y alertas.</p>
                        </div>
                        <span class="line-tag">Individual</span>
                    </div>
                    <div class="line-item">
                        <div>
                            <strong>Grupo operativo</strong>
                            <p>Promedios y elementos con refuerzo requerido por turno, celula o patrulla.</p>
                        </div>
                        <span class="line-tag">Grupo</span>
                    </div>
                    <div class="line-item">
                        <div>
                            <strong>Unidad</strong>
                            <p>Comparativo institucional para detectar brechas, avances y necesidades de capacitacion.</p>
                        </div>
                        <span class="line-tag">Unidad</span>
                    </div>
                </div>
            </article>

            <article class="panel card scoreboard-card">
                <span class="eyebrow">Tablero operativo</span>
                <h2>Indicadores listos para seguimiento por mando.</h2>

                <div class="score-row">
                    <div>
                        <span>Indice cognitivo operativo</span>
                        <strong>78/100</strong>
                    </div>
                    <div class="progress-bar"><span style="width: 78%"></span></div>
                </div>

                <div class="score-row">
                    <div>
                        <span>Elementos evaluados</span>
                        <strong>126</strong>
                    </div>
                    <div class="progress-bar"><span style="width: 68%"></span></div>
                </div>

                <div class="score-row">
                    <div>
                        <span>Refuerzo requerido</span>
                        <strong>9 alertas</strong>
                    </div>
                    <div class="progress-bar"><span style="width: 24%"></span></div>
                </div>

                <div class="hero-note">
                    El panel ya esta orientado a elementos, unidades, grupos operativos y categorias cognitivas para
                    seguridad publica.
                </div>
            </article>
        </div>
    </section>

    <section class="section">
        <article class="panel card cta-panel">
            <span class="eyebrow">Acceso institucional</span>
            <h2>Ingresa al panel institucional.</h2>
            <p class="section-copy">
                El acceso esta reservado para personal autorizado. Desde el panel se consultan indicadores, alertas y
                reportes operativos.
            </p>

            <div class="cta-row">
                <a href="{{ route('admin.showLogin') }}" class="btn btn-primary">Iniciar sesion</a>
            </div>
        </article>
    </section>
@endsection
