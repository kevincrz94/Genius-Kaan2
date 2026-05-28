@extends('admin.layouts.main')

@section('section')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h3 class="mb-1 text-primary">
                        <i class="fe fe-target me-2"></i>
                        Análisis de Brechas Cognitivas
                    </h3>
                    <p class="text-muted mb-0">Perfil táctico comparativo contra estándar operativo.</p>
                </div>
                <button class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="fe fe-printer me-1"></i>
                    Imprimir reporte
                </button>
            </div>

            <div class="card customShadow mb-4">
                <div class="card-body bg-light rounded">
                    <form method="get" action="{{ route('admin.metrics.comparative') }}" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Elemento a evaluar</label>
                            <select name="user_id" class="form-select">
                                @foreach ($users as $option)
                                    <option value="{{ $option->id }}" @selected(optional($selectedUser)->id === $option->id)>
                                        {{ $option->name }}{{ $option->badge_number ? ' / '.$option->badge_number : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold text-muted small text-uppercase">Línea base de comparación</label>
                            <select name="comparison" class="form-select">
                                <option value="unit" @selected($comparison === 'unit')>Promedio de su unidad</option>
                                <option value="group" @selected($comparison === 'group')>Promedio de su grupo operativo</option>
                                <option value="global" @selected($comparison === 'global')>Promedio global de la agencia</option>
                                <option value="optimal" @selected($comparison === 'optimal')>Estándar óptimo requerido</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fe fe-refresh-cw me-1"></i>
                                Actualizar gráficas
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if (! $selectedUser)
                <div class="alert alert-info">No hay elementos registrados para generar el análisis comparativo.</div>
            @else
                <div class="row">
                    <div class="col-lg-7">
                        <div class="card customShadow h-100">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Perfil táctico simultáneo</h5>
                                <p class="text-muted small mb-0">
                                    {{ $selectedUser->name }} contra {{ $comparisonLabel }}.
                                </p>
                            </div>
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <div class="metrics-chart-box">
                                    <canvas id="radarChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card customShadow h-100">
                            <div class="card-header bg-white border-bottom-0">
                                <h5 class="card-title mb-0">Diagnóstico operativo</h5>
                            </div>
                            <div class="card-body pt-0">
                                @php
                                    $weakness = $insights['weakness'];
                                    $strength = $insights['strength'];
                                @endphp

                                <div class="alert alert-warning border-start border-warning border-4 bg-white shadow-sm">
                                    <h6 class="text-warning fw-bold">
                                        <i class="fe fe-alert-triangle me-1"></i>
                                        Vulnerabilidad detectada
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        Brecha de <strong>{{ $weakness['diff'] }} puntos en {{ $weakness['label'] }}</strong>
                                        frente a la línea base seleccionada. Riesgo {{ $insights['risk_level'] }} para
                                        tareas con presión operativa.
                                    </p>
                                </div>

                                <div class="alert alert-success border-start border-success border-4 bg-white shadow-sm mt-3">
                                    <h6 class="text-success fw-bold">
                                        <i class="fe fe-shield me-1"></i>
                                        Fortaleza táctica
                                    </h6>
                                    <p class="small text-muted mb-0">
                                        Mejor desempeño relativo en <strong>{{ $strength['label'] }}
                                            ({{ $strength['diff'] >= 0 ? '+' : '' }}{{ $strength['diff'] }} puntos)</strong>.
                                        Útil para asignaciones compatibles con su perfil actual.
                                    </p>
                                </div>

                                <hr class="my-4">

                                <h6 class="text-muted text-uppercase small fw-bold mb-3">Recomendación de mando</h6>
                                <p class="small">
                                    {{ $insights['recommendation'] }}
                                    Área actual: <strong>{{ $insights['assignment'] }}</strong>.
                                </p>
                                <a href="{{ route('admin.metrics.user', $selectedUser) }}" class="btn btn-sm btn-outline-primary w-100">
                                    Abrir perfil de métricas
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card customShadow">
                            <div class="card-header bg-white">
                                <h5 class="card-title mb-0">Tendencia histórica del elemento</h5>
                                <p class="text-muted small mb-0">Promedio mensual de las mediciones disponibles.</p>
                            </div>
                            <div class="card-body">
                                <div class="metrics-trend-box">
                                    <canvas id="trendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radarElement = document.getElementById('radarChart');
            const trendElement = document.getElementById('trendChart');

            if (!radarElement || !trendElement || typeof Chart === 'undefined') {
                return;
            }

            const labels = @json($radarLabels);
            const userData = @json($radarUserData);
            const baselineData = @json($radarBaselineData);
            const trendData = @json($trendData);

            new Chart(radarElement.getContext('2d'), {
                type: 'radar',
                data: {
                    labels,
                    datasets: [{
                        label: '{{ optional($selectedUser)->name ?? 'Elemento evaluado' }}',
                        data: userData,
                        backgroundColor: 'rgba(13, 110, 253, 0.35)',
                        borderColor: 'rgba(13, 110, 253, 1)',
                        pointBackgroundColor: 'rgba(13, 110, 253, 1)',
                        pointBorderColor: '#fff',
                        borderWidth: 2,
                    }, {
                        label: '{{ $comparisonLabel }}',
                        data: baselineData,
                        backgroundColor: 'rgba(96, 112, 134, 0.18)',
                        borderColor: 'rgba(96, 112, 134, 0.85)',
                        borderWidth: 2,
                        borderDash: [5, 5],
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            angleLines: { color: 'rgba(0, 37, 76, 0.1)' },
                            grid: { color: 'rgba(0, 37, 76, 0.1)' },
                            pointLabels: {
                                font: { size: 12, weight: 'bold' },
                                color: '#00254c'
                            },
                            suggestedMin: 0,
                            suggestedMax: 100
                        }
                    },
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });

            new Chart(trendElement.getContext('2d'), {
                type: 'line',
                data: {
                    labels: trendData.labels,
                    datasets: [{
                        label: 'Índice mensual',
                        data: trendData.scores,
                        borderColor: 'rgba(0, 37, 76, 1)',
                        backgroundColor: 'rgba(0, 37, 76, 0.12)',
                        fill: true,
                        tension: 0.35,
                        spanGaps: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            min: 0,
                            max: 100,
                            grid: { color: 'rgba(0, 37, 76, 0.08)' }
                        },
                        x: {
                            grid: { display: false }
                        }
                    },
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        });
    </script>
@endpush
