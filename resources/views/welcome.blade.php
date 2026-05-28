@extends('layouts.public')

@section('content')
    @php
        $heroMetrics = [
            ['label' => 'Índice operativo', 'value' => '0-100', 'copy' => 'Lectura consolidada de desempeño cognitivo por elemento y unidad.'],
            ['label' => 'Categorías', 'value' => '8', 'copy' => 'Atención, reacción, memoria, control inhibitorio y toma de decisiones.'],
            ['label' => 'Seguimiento', 'value' => '24/7', 'copy' => 'Panel disponible para mandos, coordinadores y responsables de capacitación.'],
            ['label' => 'Reportes', 'value' => 'PDF', 'copy' => 'Informes individuales y comparativos para decisiones institucionales.'],
        ];

        $capabilities = [
            ['title' => 'Atención sostenida', 'accent' => '#00254c', 'copy' => 'Capacidad para mantener foco durante patrullaje, monitoreo, vigilancia o tareas prolongadas.'],
            ['title' => 'Tiempo de reacción', 'accent' => '#0d6efd', 'copy' => 'Velocidad de respuesta ante estímulos relevantes, cambios de contexto y señales de riesgo.'],
            ['title' => 'Control inhibitorio', 'accent' => '#c7a34b', 'copy' => 'Regulación de impulsos y respuesta proporcional en escenarios de presión operativa.'],
            ['title' => 'Toma de decisiones', 'accent' => '#145da0', 'copy' => 'Evaluación rápida de alternativas para actuar con criterio, prioridad y trazabilidad.'],
            ['title' => 'Memoria de trabajo', 'accent' => '#4b5f7a', 'copy' => 'Retención y uso de información táctica, instrucciones, ubicaciones y protocolos.'],
            ['title' => 'Carga mental', 'accent' => '#23395d', 'copy' => 'Seguimiento de consistencia cognitiva cuando aumenta la demanda o la fatiga.'],
        ];

        $methodSteps = [
            ['step' => '01', 'title' => 'Evaluar capacidades', 'copy' => 'Observa categorías cognitivas relacionadas con atención, reacción, memoria y decisión.'],
            ['step' => '02', 'title' => 'Medir desempeño', 'copy' => 'Ejecuta sesiones CogniFit o captura mediciones internas con puntajes normalizados.'],
            ['step' => '03', 'title' => 'Tomar acción', 'copy' => 'Consulta alertas, refuerzo requerido, comparativos y reportes por elemento o unidad.'],
        ];

        $structureLevels = [
            ['title' => 'Elemento', 'tag' => 'Individual', 'copy' => 'Ficha individual con índice operativo, categorías, historial y alertas.'],
            ['title' => 'Grupo operativo', 'tag' => 'Grupo', 'copy' => 'Promedios y elementos con refuerzo requerido por turno, célula o patrulla.'],
            ['title' => 'Unidad', 'tag' => 'Unidad', 'copy' => 'Comparativo institucional para detectar brechas, avances y necesidades de capacitación.'],
        ];

        $dashboardScores = [
            ['label' => 'Índice cognitivo operativo', 'value' => 78],
            ['label' => 'Elementos evaluados', 'value' => 68, 'display' => '126'],
            ['label' => 'Refuerzo requerido', 'value' => 24, 'display' => '9 alertas'],
        ];
    @endphp

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
                <a href="{{ route('user.login') }}" class="btn btn-primary">Ingresar al panel</a>
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
                @foreach ($heroMetrics as $metric)
                    <x-metric-card :label="$metric['label']" :value="$metric['value']">
                        {{ $metric['copy'] }}
                    </x-metric-card>
                @endforeach
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
            @foreach ($capabilities as $capability)
                <x-feature-card :title="$capability['title']" :accent-color="$capability['accent']">
                    {{ $capability['copy'] }}
                </x-feature-card>
            @endforeach
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
            @foreach ($methodSteps as $step)
                <article class="card timeline-card">
                    <span class="step-badge">{{ $step['step'] }}</span>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['copy'] }}</p>
                </article>
            @endforeach
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
                    @foreach ($structureLevels as $level)
                        <div class="line-item">
                            <div>
                                <strong>{{ $level['title'] }}</strong>
                                <p>{{ $level['copy'] }}</p>
                            </div>
                            <span class="line-tag">{{ $level['tag'] }}</span>
                        </div>
                    @endforeach
                </div>
            </article>

            <article class="panel card scoreboard-card">
                <span class="eyebrow">Tablero operativo</span>
                <h2>Indicadores listos para seguimiento por mando.</h2>

                @foreach ($dashboardScores as $score)
                    <x-progress-score-row
                        :label="$score['label']"
                        :value="$score['value']"
                        :display="$score['display'] ?? null"
                    />
                @endforeach

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
        </article>
    </section>
@endsection
