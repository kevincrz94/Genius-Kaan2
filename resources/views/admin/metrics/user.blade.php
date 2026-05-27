@extends('admin.layouts.main')

@section('section')
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
                <a href="{{ route('admin.metrics.index') }}" class="btn btn-outline-primary">Volver al panel</a>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card customShadow">
                        <div class="card-body">
                            <span class="text-muted">Índice cognitivo operativo</span>
                            <h1 class="mt-2">{{ $operationalIndex ?: 0 }}</h1>
                            <div class="small text-muted">
                                Grupo: {{ $user->operationalGroup?->name ?: 'Sin grupo' }}
                            </div>
                            <div class="small text-muted">
                                Área: {{ $user->assignment_area ?: 'Sin área asignada' }}
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
                        <div class="card-header">
                            <h5 class="mb-0">Alertas</h5>
                        </div>
                        <div class="card-body">
                            @forelse ($alerts as $alert)
                                <div class="border-bottom py-2">
                                    <span class="badge bg-warning text-dark">{{ ucfirst($alert->severity) }}</span>
                                    <strong class="d-block mt-1">{{ $alert->title }}</strong>
                                    <div class="small text-muted">{{ $alert->description }}</div>
                                </div>
                            @empty
                                <p class="text-muted mb-0">Sin alertas registradas.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
