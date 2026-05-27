@extends('layouts.public')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <span class="eyebrow">Seguridad pública</span>
            <h1>
                Aptitud cognitiva operativa para
                <span class="highlight">elementos en servicio</span>.
            </h1>
            <p class="lead">
                Genius Kaan integra evaluación, entrenamiento y seguimiento cognitivo para corporaciones de seguridad
                pública. Permite medir atención, reacción, memoria de trabajo y toma de decisiones por elemento, unidad,
                grupo operativo y categoría.
            </p>

            <div class="cta-row">
                <a href="{{ route('admin.showLogin') }}" class="btn btn-primary">Iniciar sesión</a>
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
                    <span>Índice operativo</span>
                    <strong>0-100</strong>
                    <p>Lectura consolidada de desempeño cognitivo por elemento y unidad.</p>
                </article>
                <article class="metric-card">
                    <span>Categorías</span>
                    <strong>8</strong>
                    <p>Atención, reacción, memoria, control inhibitorio y toma de decisiones.</p>
                </article>
                <article class="metric-card">
                    <span>Seguimiento</span>
                    <strong>24/7</strong>
                    <p>Panel disponible para mandos, coordinadores y responsables de capacitación.</p>
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
                    La plataforma convierte sesiones cognitivas en indicadores accionables para supervisión,
                    capacitación y seguimiento por mando.
                </p>

                <div class="checklist">
                    <div class="check-item">
                        <strong>Visión institucional</strong>
                        <span>Indicadores organizados para supervisores, mandos y responsables de capacitación.</span>
                    </div>
                    <div class="check-item">
                        <strong>Medición cognitiva</strong>
                        <span>Captura o sincroniza puntajes por categoría operativa.</span>
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
                <span class="eyebrow">Capacidades críticas</span>
                <h2>Categorías cognitivas alineadas al desempeño operativo.</h2>
            </div>
            <p class="section-copy">
                El objetivo es traducir el entrenamiento cognitivo en criterios útiles para servicio, fatiga, reacción,
                toma de decisiones y consistencia bajo carga mental.
            </p>
        </div>

        <div class="grid-3">
            <article class="card feature-card" style="--card-accent: #00254c">
                <div class="accent-dot"></div>
                <h3>Atención sostenida</h3>
                <p>Capacidad para mantener foco durante patrullaje, monitoreo, vigilancia o tareas prolongadas.</p>
            </article>
            <article class="card feature-card" style="--card-accent: #0d6efd">
                <div class="accent-dot"></div>
                <h3>Tiempo de reacción</h3>
                <p>Velocidad de respuesta ante estímulos relevantes, cambios de contexto y señales de riesgo.</p>
            </article>
            <article class="card feature-card" style="--card-accent: #c7a34b">
                <div class="accent-dot"></div>
                <h3>Control inhibitorio</h3>
                <p>Regulación de impulsos y respuesta proporcional en escenarios de presión operativa.</p>
            </article>
            <article class="card feature-card" style="--card-accent: #145da0">
                <div class="accent-dot"></div>
                <h3>Toma de decisiones</h3>
                <p>Evaluación rápida de alternativas para actuar con criterio, prioridad y trazabilidad.</p>
            </article>
            <article class="card feature-card" style="--card-accent: #4b5f7a">
                <div class="accent-dot"></div>
                <h3>Memoria de trabajo</h3>
                <p>Retención y uso de información táctica, instrucciones, ubicaciones y protocolos.</p>
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
                <span class="eyebrow">Método institucional</span>
                <h2>De la evaluación al reporte para mando.</h2>
            </div>
            <p class="section-copy">
                La operación se organiza por unidad, grupo y categoría para que los datos puedan compararse sin perder
                trazabilidad individual.
            </p>
        </div>

        <div class="grid-3">
            <article class="card timeline-card">
                <span class="step-badge">01</span>
                <h3>Evaluar capacidades</h3>
                <p>Observa categorías cognitivas relacionadas con atención, reacción, memoria y decisión.</p>
            </article>
            <article class="card timeline-card">
                <span class="step-badge">02</span>
                <h3>Medir desempeño</h3>
                <p>Ejecuta sesiones Cognifit o captura mediciones internas con puntajes normalizados.</p>
            </article>
            <article class="card timeline-card">
                <span class="step-badge">03</span>
                <h3>Tomar acción</h3>
                <p>Consulta alertas, refuerzo requerido, comparativos y reportes por elemento o unidad.</p>
            </article>
        </div>
    </section>

    <section class="section">
        <div class="dashboard">
            <article class="panel card">
                <span class="eyebrow">Mando y supervisión</span>
                <h2>Lectura por estructura operativa.</h2>
                <p class="section-copy">
                    El panel permite observar el desempeño cognitivo desde distintos niveles de decisión, sin reducir
                    el seguimiento a una lista aislada de registros.
                </p>

                <div class="stack-list">
                    <div class="line-item">
                        <div>
                            <strong>Elemento</strong>
                            <p>Ficha individual con índice operativo, categorías, historial y alertas.</p>
                        </div>
                        <span class="line-tag">Individual</span>
                    </div>
                    <div class="line-item">
                        <div>
                            <strong>Grupo operativo</strong>
                            <p>Promedios y elementos con refuerzo requerido por turno, célula o patrulla.</p>
                        </div>
                        <span class="line-tag">Grupo</span>
                    </div>
                    <div class="line-item">
                        <div>
                            <strong>Unidad</strong>
                            <p>Comparativo institucional para detectar brechas, avances y necesidades de capacitación.</p>
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
                        <span>Índice cognitivo operativo</span>
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
                    El panel ya está orientado a elementos, unidades, grupos operativos y categorías cognitivas para
                    seguridad pública.
                </div>
            </article>
        </div>
    </section>

    <section class="section">
        <article class="panel card cta-panel">
            <span class="eyebrow">Acceso institucional</span>
            <h2>Ingresa al panel institucional.</h2>
            <p class="section-copy">
                El acceso está reservado para personal autorizado. Desde el panel se consultan indicadores, alertas y
                reportes operativos.
            </p>

            <div class="cta-row">
                <a href="{{ route('admin.showLogin') }}" class="btn btn-primary">Iniciar sesión</a>
            </div>
        </article>
    </section>
@endsection
