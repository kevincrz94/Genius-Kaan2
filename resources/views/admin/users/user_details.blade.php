@php
    $viewData = \App\Services\customBlock::class;
    $image = $viewData::printData($info, 'image');
    $path = $image != '-' ? public_path('UserImages/' . $image) : null;
    $status = $viewData::printData($info, 'status') ?? 1;

    // Ye sahi tareeka hai – array ko properly handle karo
    $goals = [];
    $areas = [];

    if (isset($info['userIntrest']['goals']) && is_array($info['userIntrest']['goals'])) {
        $goals = array_values($info['userIntrest']['goals']); // string keys ko reset kar do
    }
    if (isset($info['userIntrest']['areas']) && is_array($info['userIntrest']['areas'])) {
        $areas = array_values($info['userIntrest']['areas']);
    }
@endphp

<div class="card customShadow">
    <div class="card-header">
        <div class="d-flex align-items-center justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <img src="{{ $path && file_exists($path) ? asset('UserImages/' . $image) : asset('common/favicon.png') }}"
                    class="avatar avatar-xl rounded-circle">
                <div class="d-flex flex-column gap-1">
                    <strong>{{ $viewData::printData($info, 'name') }}</strong>
                    <span class="text-muted">{{ $viewData::printData($info, 'email') }}</span>
                </div>
            </div>
            <div>
                <a href="{{ route('admin.user.report', ['id' => $info['id']]) }}" target="_blank"
                    class="btn btn-primary btn-sm btn-rounded">
                    <i class="fa fa-download"></i>
                    Download PDF
                </a>
                @if (isset($info['user_token']) && $info['user_token'] != null)
                    <button class="btn btn-warning btn-sm btn-rounded" type="button" data-bs-toggle="modal"
                        data-bs-target="#changeLocaleModal{{ $info['id'] }}">
                        <i class="fa fa-globe"></i>
                        Change Game Locale
                    </button>
                @else
                    <button class="btn btn-success btn-sm btn-rounded" type="button" data-bs-toggle="modal"
                        data-bs-target="#registerInGameModal{{ $info['id'] }}">
                        <i class="fa fa-user-plus"></i>
                        Register In Game
                    </button>
                @endif
                <button class="btn btn-danger btn-sm btn-rounded" type="button" data-bs-toggle="modal"
                    data-bs-target="#deleteModal{{ $info['id'] }}">
                    <i class="fa fa-trash"></i>
                    Delete
                </button>
            </div>
        </div>
    </div>

    <ul class="list-group list-group-flush">
        <li class="list-group-item d-flex justify-content-between">Gender:
            <span>{{ $viewData::printData($info, 'gender') }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Placa / ID:
            <span>{{ $viewData::printData($info, 'badge_number') }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Rango:
            <span>{{ $viewData::printData($info, 'rank') }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Unidad:
            <span>{{ $viewData::printData($info, 'unit') }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Grupo:
            <span>{{ $viewData::printData($info, 'operational_group') }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Area:
            <span>{{ $viewData::printData($info, 'assignment_area') }}</span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Status:
            <span class="badge badge-{{ $status == 2 ? 'danger' : ($status == 1 ? 'success' : 'warning') }}">
                {{ $status == 2 ? 'Suspended' : ($status == 1 ? 'Active' : 'Inactive') }}
            </span>
        </li>
        <li class="list-group-item d-flex justify-content-between">Joined On:
            <span>{{ \Carbon\Carbon::parse($viewData::printData($info, 'created_at'))->format('d M Y') }}</span>
        </li>
    </ul>

    <div class="card-body p-0 mt-3">
        <ul class="nav nav-tabs border-bottom" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#goals-tab">Goals ({{ count($goals) }})</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#areas-tab">Areas ({{ count($areas) }})</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#games-tab">Games</a>
            </li>
        </ul>

        <div class="tab-content p-3">
            <!-- Goals Tab -->
            <div id="goals-tab" class="tab-pane fade show active">
                @if (count($goals) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach ($goals as $goal)
                            <li class="list-group-item">{{ is_string($goal) ? $goal : $goal['name'] ?? 'Unknown' }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No goals selected</p>
                @endif
            </div>

            <!-- Areas Tab -->
            <div id="areas-tab" class="tab-pane fade">
                @if (count($areas) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach ($areas as $area)
                            <li class="list-group-item">{{ is_string($area) ? $area : $area['name'] ?? 'Unknown' }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted">No areas selected</p>
                @endif
            </div>

            <!-- Games Tab -->
            <div id="games-tab" class="tab-pane fade">
                @if (!empty($playedGames))
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Game</th>
                                    <th>Score</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (array_slice($playedGames, 0, 10) as $game)
                                    <tr>
                                        <td>{{ $brainGames->where('key', $game['key'] ?? '')->first()->assets->titles->en ?? ($game['key'] ?? 'Unknown') }}
                                        </td>
                                        <td>{{ $game['score'] ?? '-' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($game['time'] ?? now())->format('d M Y') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted text-center py-3">No games played yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Modal For Deletion of the user --}}
<div class="modal fade" id="deleteModal{{ $info['id'] }}" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Delete {{ $info['name'] }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <span class="fw-bold">{{ $info['name'] }}</span> with email <span
                        class="fw-bold">{{ $info['email'] }}</span>?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="{{ route('admin.users.destroy', ['id' => $info['id']]) }}" class="btn btn-danger">
                    Yes
                </a>
            </div>
        </div>
    </div>
</div>
{{-- End Modal --}}

{{-- This modal is working for the changing of the locale of the game --}}
<div class="modal fade" id="changeLocaleModal{{ $info['id'] }}" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Change Locale</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.update.game.locale') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <input type="hidden" name="user_token" value="{{ $info['user_token'] }}">
                        <label for="">Game Locale</label>
                        <select name="locale" id="locale" class="form-control" required>
                            <option value="">Select Locale</option>
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i>
                        Close
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i>
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- End modal --}}

{{-- This modal is working for the registering the user in the game mode --}}
<div class="modal fade" id="registerInGameModal{{ $info['id'] }}" tabindex="-1"
    aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Register {{ $info['name'] }}</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.register.user.game') }}" method="post">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info" role="alert">
                        <h4 class="alert-heading">Register {{ $info['name'] }}</h4>
                        <p>
                            Are you sure you want to register {{ $info['name'] }} in the game mode?
                        </p>
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="user_id" value="{{ $info['id'] }}">
                        <label for="locale">Game Locale</label>
                        <select name="locale" id="locale" class="form-control" required>
                            <option value="">Select Locale</option>
                            <option value="en">English</option>
                            <option value="es">Spanish</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
{{-- End Modal --}}
