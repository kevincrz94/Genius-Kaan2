@extends('admin.layouts.main')

@section('section')
    @php
        $viewData = \App\Services\customBlock::class;
        $image = $viewData::printData($info, 'image');
        $imagePath = $image !== '-' ? public_path('profiles/' . $image) : null;
        $status = $viewData::printData($info, 'status');
        $status = $status === '-' ? 1 : $status;
        $userToken = $viewData::printData($info, 'user_token');
        $hasCredential = $userToken !== '-';
        $goals = array_values($info['userIntrest']['goals'] ?? []);
        $areas = array_values($info['userIntrest']['areas'] ?? []);
        $titleMap = $brainGameTitles ?? [];
    @endphp

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-lg-3">
                    <div class="card customShadow">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <div class="d-flex gap-2 align-items-center">
                                    <img class="avatar-img avatar-xl rounded-circle"
                                        src="{{ $imagePath && file_exists($imagePath) ? asset('profiles/' . $image) : asset('common/favicon.png') }}"
                                        alt="Elemento">
                                    <div class="d-flex flex-column gap-2">
                                        <a href="javascript:void(0);">{{ $viewData::printData($info, 'name') }}</a>
                                        <a class="text-muted" href="javascript:void(0)">
                                            {{ $viewData::printData($info, 'email') }}
                                        </a>
                                    </div>
                                </div>
                            </h5>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Género:
                                <span class="fw-bold">{{ $viewData::printData($info, 'gender') }}</span>
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
                                        Áreas ({{ count($areas) }})
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
                                                @forelse ($areas as $item)
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
@endsection
