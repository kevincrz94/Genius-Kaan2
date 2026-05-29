@extends('admin.layouts.main')

@section('section')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Panel de aptitud cognitiva operativa</h3>
                    <p class="text-muted mb-0">Seguimiento por elemento, unidad, grupo y categoría.</p>
                </div>
                <form method="post" action="{{ route('admin.metrics.sync-cognifit') }}">
                    @csrf
                    @foreach ($filters as $key => $value)
                        @if (filled($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach
                    <button type="submit" class="btn btn-outline-success">
                        <i class="fa fa-rotate me-1"></i>
                        Sincronizar pendientes
                    </button>
                </form>
                <a href="{{ route('admin.metrics.comparative') }}" class="btn btn-outline-primary">
                    <i class="fe fe-target me-1"></i>
                    Análisis comparativo
                </a>
            </div>

            <div class="alert alert-info d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <strong>Estado CogniFit:</strong>
                    {{ $syncSummary['pending'] }} sesiones en procesamiento,
                    {{ $syncSummary['due'] }} listas para revisar,
                    {{ $syncSummary['failed'] }} con revisiÃ³n requerida.
                </div>
                <span class="small text-muted">La sincronizaciÃ³n manual consulta solo sesiones vencidas o pendientes.</span>
            </div>

            <div class="card customShadow mb-3">
                <div class="card-body">
                    <form method="get" action="{{ route('admin.metrics.index') }}" class="row g-3">
                        <div class="col-lg-3">
                            <label class="form-label">Unidad</label>
                            <select name="security_unit_id" class="form-control">
                                <option value="">Todas</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" @selected(($filters['security_unit_id'] ?? '') == $unit->id)>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Grupo operativo</label>
                            <select name="operational_group_id" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}" @selected(($filters['operational_group_id'] ?? '') == $group->id)>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Categoría</label>
                            <select name="category" class="form-control">
                                <option value="">Todas</option>
                                @foreach ($categories as $key => $label)
                                    <option value="{{ $key }}" @selected(($filters['category'] ?? '') === $key)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Elemento</label>
                            <select name="user_id" class="form-control">
                                <option value="">Todos</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" @selected(($filters['user_id'] ?? '') == $user->id)>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 d-flex align-items-end gap-2 ms-auto">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fe fe-filter"></i> Filtrar
                            </button>
                            @if (request()->hasAny(['security_unit_id', 'operational_group_id', 'category', 'user_id']))
                                <a href="{{ route('admin.metrics.index') }}" class="btn btn-outline-secondary px-3"
                                    title="Limpiar filtros" aria-label="Limpiar filtros">
                                    <i class="fe fe-x"></i>
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <div class="row">
                @foreach ([
                    ['label' => 'Elementos', 'value' => $summary['elements']],
                    ['label' => 'Evaluados', 'value' => $summary['evaluated']],
                    ['label' => 'Índice operativo', 'value' => $summary['operational_index'] ?: '0'],
                    ['label' => 'Alertas activas', 'value' => $summary['active_alerts']],
                    ['label' => 'Refuerzo requerido', 'value' => $summary['reinforcement_required']],
                ] as $card)
                    <div class="col-md">
                        <div class="card customShadow">
                            <div class="card-body">
                                <span class="text-muted">{{ $card['label'] }}</span>
                                <h3 class="mt-2 mb-0">{{ $card['value'] }}</h3>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row mt-3">
                <div class="col-lg-6">
                    <div class="card customShadow h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Promedio por categoría</h5>
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
                                            <td colspan="3" class="text-center text-muted py-4">Sin métricas registradas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card customShadow h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Ranking de elementos</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Elemento</th>
                                        <th>Unidad</th>
                                        <th class="text-center">Índice</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($elementRanking->take(10) as $row)
                                        <tr>
                                            <td>
                                                <a href="{{ $row['id'] ? route('admin.metrics.user', $row['id']) : '#' }}">
                                                    {{ $row['name'] }}
                                                </a>
                                                <div class="small text-muted">{{ $row['badge_number'] ?: 'Sin placa' }}</div>
                                            </td>
                                            <td>{{ $row['unit'] }}</td>
                                            <td class="text-center fw-bold">{{ $row['score'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Sin elementos evaluados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-6">
                    <div class="card customShadow h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Promedio por unidad</h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Unidad</th>
                                        <th>Elementos</th>
                                        <th class="text-center">Puntaje</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($unitAverages as $row)
                                        <tr>
                                            <td>{{ $row['name'] }}</td>
                                            <td>{{ $row['elements'] }}</td>
                                            <td class="text-center fw-bold">{{ $row['score'] }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Sin datos por unidad.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card customShadow h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Alertas y refuerzo</h5>
                        </div>
                        <div class="card-body">
                            @forelse ($riskElements->take(8) as $row)
                                @php
                                    $scoreColor = $row['score'] < 50 ? 'bg-danger text-white' : 'bg-warning text-dark';
                                @endphp
                                <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                    <div>
                                        <strong>{{ $row['name'] }}</strong>
                                        <div class="small text-muted">{{ $row['unit'] }} / {{ $row['group'] }}</div>
                                    </div>
                                    <span class="badge {{ $scoreColor }} px-2 py-1 fs-6">
                                        {{ $row['score'] }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-muted mb-0">
                                    <i class="fe fe-shield text-success me-1"></i>
                                    Sin elementos en rango crítico.
                                </p>
                            @endforelse

                            @foreach ($activeAlerts as $alert)
                                @php
                                    $alertClass = match (strtolower($alert->severity)) {
                                        'alta', 'crítica', 'critica', 'high', 'critical' => 'alert-danger',
                                        'media', 'medium' => 'alert-warning',
                                        default => 'alert-info',
                                    };
                                @endphp
                                <div class="alert {{ $alertClass }} mt-3 mb-0">
                                    <strong>{{ $alert->title }}</strong>
                                    <div class="small">{{ $alert->description }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
