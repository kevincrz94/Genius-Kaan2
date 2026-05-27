@extends('admin.layouts.main')

@section('section')
    @php
        $viewData = \app\Services\customBlock::class;
    @endphp

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-lg-3">
                    <div class="card customShadow">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <div class="d-flex gap-2 align-items-center">
                                    @php
                                        $image = $viewData::printData($info, 'image'); // Get image or '-'
                                        $imagePath = $image !== '-' ? public_path('profiles/' . $image) : null;
                                    @endphp
                                    <img class="avatar-img avatar-xl rounded-circle"
                                        src="{{ $imagePath && file_exists($imagePath) ? asset('profiles/' . $image) : asset('common/favicon.png') }}"
                                        alt="Elemento" style="">
                                    <div class="d-flex flex-column gap-2">
                                        <a href="javascript:void(0);">{{ $viewData::printData($info, 'name') }}</a>
                                        <a class="text-muted" href="javascript:void(0)"
                                            style="font-size: 14px">{{ $viewData::printData($info, 'email') }}</a>
                                    </div>
                                </div>
                            </h5>
                        </div>
                        <ul class="list-group list-group-flush">
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Género:
                                <span class="fw-bold">
                                    {{ $viewData::printData($info, 'gender') }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Puntaje base:
                                <span class="fw-bold">
                                    {{-- @dd($gameData) --}}
                                    {{ $gameData['baseScore'] ?? '-' }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Estado:
                                @php
                                    $getStatus = $viewData::printData($info, 'status');

                                    $status = $getStatus == '-' ? '1' : $getStatus;
                                @endphp
                                <span
                                    class="badge badge-{{ $status == 2 ? 'danger' : ($status == 1 ? 'success' : 'warning') }}">
                                    {{ $status == 2 ? 'Suspendido' : ($status == 1 ? 'Activo' : 'Inactivo') }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Juegos realizados:
                                @php
                                    $getUserToken = $viewData::printData($info, 'user_token');

                                    $gamePlayed = $getUserToken != '-' ? '1' : '2';
                                @endphp
                                <span
                                    class="badge badge-{{ $gamePlayed == 2 ? 'danger' : ($gamePlayed == 1 ? 'success' : 'warning') }}">
                                    {{ $gamePlayed == 2 ? 'No' : ($gamePlayed == 1 ? 'Sí' : 'Inactivo') }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Fecha de alta:
                                <span class="fw-bold">
                                    {{ \Carbon\Carbon::parse($viewData::printData($info, 'created_at'))->format(' M Y') }}
                                </span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="card customShadow">
                        <div class="card-header p-0 pt-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <ul class="nav nav-tabs nav-tabs-bottom" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" href="#bottom-tab1" data-bs-toggle="tab"
                                            aria-selected="true" role="tab">
                                            @php
                                                $goalsArray = isset($info['userIntrest']['goals'])
                                                    ? $info['userIntrest']['goals']
                                                    : [];
                                            @endphp
                                            Objetivos ({{ count($goalsArray) }})
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" href="#bottom-tab2" data-bs-toggle="tab" aria-selected="false"
                                            tabindex="-1" role="tab">
                                            @php
                                                $goalsArray = isset($info['userIntrest']['areas'])
                                                    ? $info['userIntrest']['areas']
                                                    : [];
                                            @endphp
                                            Áreas ({{ count($goalsArray) }})
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" href="#bottom-tab3" data-bs-toggle="tab" aria-selected="false"
                                            tabindex="-1" role="tab">
                                            Juegos
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="tab-content p-0 px-2 py-2">
                                <div class="tab-pane show active" id="bottom-tab1" role="tabpanel">
                                    @php
                                        $goalsArray = isset($info['userIntrest']['areas'])
                                            ? $info['userIntrest']['areas']
                                            : [];
                                    @endphp

                                    <div class="table-responsive">
                                        <table class="datatable table table-stripped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($goalsArray as $key => $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $viewData::printData($goalsArray, $key) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane" id="bottom-tab2" role="tabpanel">
                                    @php
                                        $goalsArray = isset($info['userIntrest']['goals'])
                                            ? $info['userIntrest']['goals']
                                            : [];
                                    @endphp

                                    <div class="table-responsive">
                                        <table class="datatable table table-stripped">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($goalsArray as $key => $item)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $viewData::printData($goalsArray, $key) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane" id="bottom-tab3" role="tabpanel">
                                    <div class="table-responsive">
                                        @php
                                            $titleMap = [];
                                            foreach ($brainGames as $game) {
                                                if (isset($game->key, $game->assets->titles->en)) {
                                                    $titleMap[$game->key] = $game->assets->titles->es ?? $game->assets->titles->en;
                                                }
                                            }
                                        @endphp

                                        <table class="datatable table table-stripped">
                                            <thead>
                                                <tr>
                                                    <th>Juego</th>
                                                    <th>Nivel</th>
                                                    <th>Subnivel</th>
                                                    <th>Puntaje</th>
                                                    <th>Tiempo jugado (seg)</th>
                                                    <th>Fecha y hora</th>
                                                    <th>Motivo de salida</th>
                                                    <th>Tipo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($playedGames as $game)
                                                    <tr>
                                                        <td>
                                                            {{ $titleMap[$game['key']] ?? 'Juego desconocido' }}
                                                        </td>
                                                        <td>{{ $game['level'] ?? '-' }}</td>
                                                        <td>{{ $game['sublevel'] ?? '-' }}</td>
                                                        <td>{{ $game['score'] ?? '-' }}</td>
                                                        <td>{{ $game['timePlayed'] ?? '-' }}</td>
                                                        <td>
                                                            {{ \Carbon\Carbon::parse($game['time'])->timezone('Asia/Karachi')->format('d M Y, h:i A') }}
                                                        </td>
                                                        <td>{{ $game['outReasonKey'] ?? '-' }}</td>
                                                        <td>{{ $game['type'] ?? '-' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="8" class="text-center">Sin juegos registrados.</td>
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
