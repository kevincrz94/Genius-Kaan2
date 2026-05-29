@extends('admin.layouts.main')

@section('section')
    @php
        $viewData = \App\Services\customBlock::class;
        $image = $viewData::printData($info, 'image');
        $imagePath = $image !== '-' ? public_path('UserImages/' . $image) : null;
        $status = $viewData::printData($info, 'status');
        $status = $status === '-' ? 1 : $status;
        $userToken = $viewData::printData($info, 'user_token');
        $hasCredential = $userToken !== '-';
        $goals = array_values($info['userIntrest']['goals'] ?? []);
        $interestAreas = $interestAreas ?? array_values($info['userIntrest']['areas'] ?? []);
        $titleMap = $brainGameTitles ?? [];
        $roleLabel = $roleLabels[$info['role'] ?? 'user'] ?? 'Usuario operativo';
        $genderLabel = $genderLabels[$info['gender'] ?? ''] ?? 'Sin género';
        $canSync = ($info['role'] ?? 'user') === 'user' && ! $hasCredential;
    @endphp

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h3 class="mb-1 text-primary"><i class="fa fa-id-card me-2"></i>{{ $title ?? 'Perfil del elemento' }}</h3>
                    <p class="text-muted mb-0">Consulta el historial del elemento y administra su ficha operativa.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.user.management') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left me-1"></i>
                        Volver
                    </a>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editUserModal">
                        <i class="fa fa-pen me-1"></i>
                        Editar perfil
                    </button>
                    <a href="{{ route('admin.user.report', ['id' => $info['id']]) }}" target="_blank" class="btn btn-primary">
                        <i class="fa fa-file-pdf me-1"></i>
                        Imprimir reporte
                    </a>
                    @if (($syncSummary['pending'] ?? 0) > 0 || $hasCredential)
                        <form method="post" action="{{ route('admin.metrics.user.sync-cognifit', ['user' => $info['id']]) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-success">
                                <i class="fa fa-rotate me-1"></i>
                                Forzar sincronizaciÃ³n
                            </button>
                        </form>
                    @endif
                    @if ($canSync)
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#syncCognifitModal">
                            <i class="fa fa-link me-1"></i>
                            Sincronizar CogniFit
                        </button>
                    @endif
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3">
                    <div class="card customShadow">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <div class="d-flex gap-2 align-items-center">
                                    <img class="avatar-img avatar-xl rounded-circle"
                                        src="{{ $imagePath && file_exists($imagePath) ? asset('UserImages/' . $image) : asset('common/favicon.png') }}"
                                        alt="Elemento">
                                    <div class="d-flex flex-column gap-2">
                                        <a href="javascript:void(0);">{{ $viewData::printData($info, 'name') }}</a>
                                        <a class="text-muted" href="javascript:void(0)">
                                            {{ $viewData::printData($info, 'email') }}
                                        </a>
                                        <span class="badge bg-light text-dark border">{{ $roleLabel }}</span>
                                    </div>
                                </div>
                            </h5>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Resultados en proceso:
                                <span class="badge bg-info">{{ $syncSummary['pending'] ?? 0 }}</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                RevisiÃ³n requerida:
                                <span class="badge bg-warning text-dark">{{ $syncSummary['failed'] ?? 0 }}</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Género:
                                <span class="fw-bold">{{ $genderLabel }}</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Puntaje base:
                                <span class="fw-bold">{{ $gameData['baseScore'] ?? '-' }}</span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Estado:
                                <span
                                    class="badge badge-{{ $status == 2 ? 'danger' : ($status == 1 ? 'success' : 'warning') }}">
                                    {{ $status == 2 ? 'Suspendido' : ($status == 1 ? 'Activo' : 'Inactivo') }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Credencial CogniFit:
                                <span class="badge badge-{{ $hasCredential ? 'success' : 'danger' }}">
                                    {{ $hasCredential ? 'Activa' : 'Pendiente' }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Fecha de alta:
                                <span class="fw-bold">
                                    {{ \Carbon\Carbon::parse($viewData::printData($info, 'created_at'))->format('M Y') }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="card customShadow">
                        <div class="card-header p-0 pt-2">
                            <ul class="nav nav-tabs nav-tabs-bottom" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link active" href="#bottom-tab1" data-bs-toggle="tab"
                                        aria-selected="true" role="tab">
                                        Objetivos ({{ count($goals) }})
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" href="#bottom-tab2" data-bs-toggle="tab" aria-selected="false"
                                        tabindex="-1" role="tab">
                                        Áreas ({{ count($interestAreas) }})
                                    </a>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <a class="nav-link" href="#bottom-tab3" data-bs-toggle="tab" aria-selected="false"
                                        tabindex="-1" role="tab">
                                        Evaluaciones
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body p-0">
                            <div class="tab-content p-0 px-2 py-2">
                                <div class="tab-pane show active" id="bottom-tab1" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="datatable table table-stripped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($goals as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ is_string($item) ? $item : $item['name'] ?? 'Sin nombre' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center">Sin objetivos seleccionados.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane" id="bottom-tab2" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="datatable table table-stripped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($interestAreas as $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ is_string($item) ? $item : $item['name'] ?? 'Sin nombre' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="2" class="text-center">Sin áreas seleccionadas.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane" id="bottom-tab3" role="tabpanel">
                                    <div class="table-responsive">
                                        <table class="datatable table table-stripped">
                                            <thead>
                                                <tr>
                                                    <th>Evaluación</th>
                                                    <th>Nivel</th>
                                                    <th>Subnivel</th>
                                                    <th>Puntaje</th>
                                                    <th>Tiempo entrenado (seg)</th>
                                                    <th>Fecha y hora</th>
                                                    <th>Motivo de salida</th>
                                                    <th>Tipo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($playedGames as $activity)
                                                    <tr>
                                                        <td>{{ $titleMap[$activity['key']] ?? 'Evaluación sin identificar' }}</td>
                                                        <td>{{ $activity['level'] ?? '-' }}</td>
                                                        <td>{{ $activity['sublevel'] ?? '-' }}</td>
                                                        <td>{{ $activity['score'] ?? '-' }}</td>
                                                        <td>{{ $activity['timePlayed'] ?? '-' }}</td>
                                                        <td>
                                                            {{ isset($activity['time']) ? \Carbon\Carbon::parse($activity['time'])->format('d M Y, h:i A') : '-' }}
                                                        </td>
                                                        <td>{{ $activity['outReasonKey'] ?? '-' }}</td>
                                                        <td>{{ $activity['type'] ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center">Sin evaluaciones registradas.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('admin.users.partials.user-form-modal', [
        'modalId' => 'editUserModal',
        'title' => 'Modificar elemento',
        'action' => route('admin.users.update', ['id' => $info['id']]),
        'method' => 'PUT',
        'user' => $info,
        'ranks' => $ranks,
        'units' => $units,
        'groups' => $groups,
        'areas' => $catalogAreas,
        'roleLabels' => $roleLabels,
        'genderLabels' => $genderLabels,
    ])

    @if ($canSync)
        <div class="modal fade" id="syncCognifitModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Sincronizar CogniFit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form action="{{ route('admin.register.user.game', ['id' => $info['id']]) }}" method="post">
                        @csrf
                        <div class="modal-body">
                            <p class="text-muted">
                                Se solicitará a CogniFit la credencial de entrenamiento para
                                <strong>{{ $viewData::printData($info, 'name') }}</strong>.
                            </p>
                            <div class="form-group mb-0">
                                <label for="syncLocale">Idioma de la evaluación</label>
                                <select id="syncLocale" name="locale" class="form-control" required>
                                    <option value="es" selected>Español</option>
                                    <option value="en">Inglés</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-link me-1"></i>
                                Sincronizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
