@php
    $status = $info['status'] ?? 1;
    $image = $info['image'] ?? null;
    $imagePath = $image ? public_path('UserImages/' . $image) : null;
    $avatar = $imagePath && file_exists($imagePath) ? asset('UserImages/' . $image) : asset('common/favicon.png');
@endphp

<div class="card customShadow">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ $avatar }}" class="avatar avatar-xl rounded-circle" alt="Elemento">
                <div class="d-flex flex-column gap-1">
                    <strong>{{ $info['name'] ?? 'Sin nombre' }}</strong>
                    <span class="text-muted">{{ $info['email'] ?? 'Sin correo' }}</span>
                </div>
            </div>
            <div>
                <a href="{{ route('admin.user.report', ['id' => $info['id']]) }}" target="_blank"
                    class="btn btn-primary btn-sm btn-rounded">
                    <i class="fa fa-download"></i>
                    Descargar PDF
                </a>
                @if (($info['role'] ?? 'user') === 'user' && filled($info['user_token'] ?? null))
                    <button class="btn btn-warning btn-sm btn-rounded" type="button" data-bs-toggle="modal"
                        data-bs-target="#changeLocaleModal{{ $info['id'] }}">
                        <i class="fa fa-globe"></i>
                        Cambiar idioma
                    </button>
                @elseif (($info['role'] ?? 'user') === 'user')
                    <button class="btn btn-success btn-sm btn-rounded" type="button" data-bs-toggle="modal"
                        data-bs-target="#registerInGameModal{{ $info['id'] }}">
                        <i class="fa fa-user-plus"></i>
                        Reintentar alta CogniFit
                    </button>
                @endif
                <button class="btn btn-danger btn-sm btn-rounded" type="button" data-bs-toggle="modal"
                    data-bs-target="#deleteModal{{ $info['id'] }}">
                    <i class="fa fa-trash"></i>
                    Eliminar
                </button>
            </div>
        </div>
    </div>

    <ul class="list-group list-group-flush">
        <li class="list-group-item d-flex justify-content-between">Perfil:
            <span>{{ str_replace('_', ' ', $info['role'] ?? 'user') }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Género:
            <span>{{ $info['gender'] ?? '-' }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Placa / ID:
            <span>{{ $info['badge_number'] ?? '-' }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Rango:
            <span>{{ $info['rank'] ?? '-' }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Unidad:
            <span>{{ $info['unit'] ?? '-' }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Grupo:
            <span>{{ $info['operational_group'] ?? '-' }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Área:
            <span>{{ $info['assignment_area'] ?? '-' }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Estado:
            <span class="badge badge-{{ $status == 2 ? 'danger' : ($status == 1 ? 'success' : 'warning') }}">
                {{ $status == 2 ? 'Suspendido' : ($status == 1 ? 'Activo' : 'Inactivo') }}
            </span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Fecha de alta:
            <span>{{ filled($info['created_at'] ?? null) ? \Carbon\Carbon::parse($info['created_at'])->format('d M Y') : '-' }}</span>
        </li>
    </ul>

    <div class="card-body p-0 mt-3">
        <ul class="nav nav-tabs border-bottom" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#goals-tab">Objetivos ({{ count($goals ?? []) }})</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#areas-tab">Áreas ({{ count($areas ?? []) }})</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#training-tab">Evaluaciones</a>
            </li>
        </ul>

        <div class="tab-content p-3">
            <div id="goals-tab" class="tab-pane fade show active">
                @if (count($goals ?? []) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach ($goals as $goal)
                            <li class="list-group-item">{{ is_string($goal) ? $goal : $goal['name'] ?? 'Sin nombre' }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">Sin objetivos seleccionados.</p>
                @endif
            </div>

            <div id="areas-tab" class="tab-pane fade">
                @if (count($areas ?? []) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach ($areas as $area)
                            <li class="list-group-item">{{ is_string($area) ? $area : $area['name'] ?? 'Sin nombre' }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">Sin áreas seleccionadas.</p>
                @endif
            </div>

            <div id="training-tab" class="tab-pane fade">
                @if (! empty($playedGames) || (($localSessions ?? collect())->isNotEmpty()))
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Evaluación</th>
                                    <th>Puntaje</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse (array_slice($playedGames, 0, 10) as $activity)
                                    <tr>
                                        <td>{{ $brainGameTitles[$activity['key'] ?? ''] ?? ($activity['key'] ?? 'Sin nombre') }}</td>
                                        <td>{{ $activity['score'] ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($activity['time'] ?? now())->format('d M Y') }}</td>
                                    </tr>
                                @empty
                                    @foreach (($localSessions ?? collect())->take(10) as $session)
                                        <tr>
                                            <td>{{ $session->game_key ?? 'Sesión CogniFit' }}</td>
                                            <td>{{ $session->score ?? '-' }}</td>
                                            <td>{{ optional($session->completed_at)->format('d M Y') ?: optional($session->created_at)->format('d M Y') }}</td>
                                        </tr>
                                    @endforeach
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">Sin evaluaciones registradas.</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal{{ $info['id'] }}" tabindex="-1" aria-labelledby="deleteModalLabel{{ $info['id'] }}"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="deleteModalLabel{{ $info['id'] }}">Eliminar {{ $info['name'] }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p>Confirma que deseas eliminar a <span class="fw-bold">{{ $info['name'] }}</span> con correo <span
                        class="fw-bold">{{ $info['email'] }}</span>.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <form action="{{ route('admin.users.destroy', ['id' => $info['id']]) }}" method="post">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        Sí, eliminar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changeLocaleModal{{ $info['id'] }}" tabindex="-1"
    aria-labelledby="changeLocaleModalLabel{{ $info['id'] }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="changeLocaleModalLabel{{ $info['id'] }}">Cambiar idioma</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('admin.update.game.locale', ['id' => $info['id']]) }}" method="post">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" name="user_token" value="{{ $info['user_token'] }}">
                        <label for="locale{{ $info['id'] }}">Idioma de la evaluación</label>
                        <select name="locale" id="locale{{ $info['id'] }}" class="form-control" required>
                            <option value="">Selecciona idioma</option>
                            <option value="en">Inglés</option>
                            <option value="es">Español</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i>
                        Cerrar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        Actualizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="registerInGameModal{{ $info['id'] }}" tabindex="-1"
    aria-labelledby="registerInGameModalLabel{{ $info['id'] }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="registerInGameModalLabel{{ $info['id'] }}">Registrar {{ $info['name'] }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form action="{{ route('admin.register.user.game', ['id' => $info['id']]) }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <h4 class="alert-heading">Registrar {{ $info['name'] }}</h4>
                        <p>
                            El alta en CogniFit se intenta automáticamente al crear el elemento. Usa esta acción solo
                            para reintentar si la credencial quedó pendiente.
                        </p>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="user_id" value="{{ $info['id'] }}">
                        <label for="registerLocale{{ $info['id'] }}">Idioma de la evaluación</label>
                        <select name="locale" id="registerLocale{{ $info['id'] }}" class="form-control" required>
                            <option value="">Selecciona idioma</option>
                            <option value="en">Inglés</option>
                            <option value="es">Español</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">
                        Registrar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
