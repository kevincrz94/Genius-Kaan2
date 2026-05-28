@extends('admin.layouts.main')

@section('section')
    @php
        $indexValue = max(0, min(100, (float) ($operationalIndex ?: 0)));
    @endphp

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">{{ $user->name }}</h3>
                    <p class="text-muted mb-0">
                        {{ $user->badge_number ?: 'Sin placa' }} /
                        {{ $user->rank ?: 'Sin rango' }} /
                        {{ $user->securityUnit?->name ?: 'Sin unidad' }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.metrics.comparative', ['user_id' => $user->id]) }}" class="btn btn-primary">
                        <i class="fe fe-target me-1"></i>
                        Comparar brechas
                    </a>
                    <a href="{{ route('admin.metrics.index') }}" class="btn btn-outline-primary">Volver al panel</a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card customShadow border-start border-primary border-3">
                        <div class="card-body">
                            <span class="text-muted text-uppercase small">Índice cognitivo operativo</span>
                            <div class="d-flex align-items-baseline mt-2">
                                <h1 class="mb-0 display-5 fw-bold">{{ $operationalIndex ?: 0 }}</h1>
                                <span class="text-muted ms-1 fs-5">/ 100</span>
                            </div>

                            <div class="progress progress-sm mt-3" role="progressbar"
                                aria-label="Índice cognitivo operativo" aria-valuenow="{{ $indexValue }}"
                                aria-valuemin="0" aria-valuemax="100">
                                <div class="progress-bar bg-primary" style="width: {{ $indexValue }}%"></div>
                            </div>

                            <div class="mt-3 pt-3 border-top small text-muted">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Grupo operativo:</span>
                                    <strong>{{ $user->operationalGroup?->name ?: 'No asignado' }}</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Área de servicio:</span>
                                    <strong>{{ $user->assignment_area ?: 'No especificada' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card customShadow mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">Registrar medición</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('admin.metrics.user.store', $user) }}">
                                @csrf
                                <div class="form-group">
                                    <label>Categoría</label>
                                    <select name="category" class="form-control" required>
                                        @foreach ($categories as $key => $label)
                                            <option value="{{ $key }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Métrica</label>
                                    <input type="text" name="metric_name" class="form-control"
                                        placeholder="Ej. Evaluación de atención dividida">
                                </div>
                                <div class="form-group">
                                    <label>Puntaje 0-100</label>
                                    <input type="number" name="score" min="0" max="100" step="0.01"
                                        class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label>Tendencia</label>
                                    <select name="trend" class="form-control">
                                        <option value="stable">Estable</option>
                                        <option value="up">Mejora</option>
                                        <option value="down">Baja</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Fecha</label>
                                    <input type="date" name="measured_at" class="form-control" value="{{ now()->toDateString() }}">
                                </div>
                                <button class="btn btn-primary w-100">Guardar medición</button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card customShadow">
                        <div class="card-header">
                            <h5 class="mb-0">Categorías operativas</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Categoría</th>
                                        <th class="text-center">Puntaje</th>
                                        <th>Nivel</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($categoryAverages as $row)
                                        <tr>
                                            <td>{{ $row['category'] }}</td>
                                            <td class="text-center fw-bold">{{ $row['score'] }}</td>
                                            <td><span class="badge bg-secondary">{{ ucfirst($row['level']) }}</span></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Sin métricas para este elemento.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-8">
                    <div class="card customShadow">
                        <div class="card-header">
                            <h5 class="mb-0">Historial de mediciones</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Categoría</th>
                                        <th>Métrica</th>
                                        <th class="text-center">Puntaje</th>
                                        <th>Fuente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($metrics->take(30) as $metric)
                                        <tr>
                                            <td>{{ optional($metric->measured_at)->format('d/m/Y') ?: '-' }}</td>
                                            <td>{{ str_replace('_', ' ', ucfirst($metric->category)) }}</td>
                                            <td>{{ $metric->metric_name }}</td>
                                            <td class="text-center fw-bold">{{ $metric->score }}</td>
                                            <td>{{ $metric->source }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Sin historial.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card customShadow">
                        <div class="card-header bg-white">
                            <h5 class="mb-0 text-danger">
                                <i class="fe fe-alert-circle me-1"></i>
                                Alertas operativas
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush">
                                @forelse ($alerts as $alert)
                                    @php
                                        $badgeClass = match (strtolower($alert->severity)) {
                                            'alta', 'crítica', 'critica', 'high', 'critical' => 'bg-danger',
                                            'media', 'medium' => 'bg-warning text-dark',
                                            default => 'bg-info',
                                        };
                                    @endphp
                                    <div class="list-group-item py-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <strong class="d-block">{{ $alert->title }}</strong>
                                            <span class="badge {{ $badgeClass }}">{{ strtoupper($alert->severity) }}</span>
                                        </div>
                                        <div class="small text-muted">{{ $alert->description }}</div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center">
                                        <i class="fe fe-check-circle text-success fs-1 mb-2"></i>
                                        <p class="text-muted mb-0">
                                            El elemento no presenta alertas de capacidad en este momento.
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
