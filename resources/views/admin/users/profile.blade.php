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
                                        alt="User Image" style="">
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
                                Gender:
                                <span class="fw-bold">
                                    {{ $viewData::printData($info, 'gender') }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Base Score:
                                <span class="fw-bold">
                                    {{-- @dd($gameData) --}}
                                    {{ $gameData['baseScore'] ?? '-' }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Status:
                                @php
                                    $getStatus = $viewData::printData($info, 'status');

                                    $status = $getStatus == '-' ? '1' : $getStatus;
                                @endphp
                                <span
                                    class="badge badge-{{ $status == 2 ? 'danger' : ($status == 1 ? 'success' : 'warning') }}">
                                    {{ $status == 2 ? 'Suspended' : ($status == 1 ? 'Active' : 'Inactive') }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Games Played:
                                @php
                                    $getUserToken = $viewData::printData($info, 'user_token');

                                    $gamePlayed = $getUserToken != '-' ? '1' : '2';
                                @endphp
                                <span
                                    class="badge badge-{{ $gamePlayed == 2 ? 'danger' : ($gamePlayed == 1 ? 'success' : 'warning') }}">
                                    {{ $gamePlayed == 2 ? 'No' : ($gamePlayed == 1 ? 'Yes' : 'Inactive') }}
                                </span>
                            </li>
                            <li class="d-flex justify-content-between align-items-center list-group-item">
                                Joined on:
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
                                            Goals ({{ count($goalsArray) }})
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
                                            Areas ({{ count($goalsArray) }})
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" href="#bottom-tab3" data-bs-toggle="tab" aria-selected="false"
                                            tabindex="-1" role="tab">
                                            Games
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
                                                    <th>Name</th>
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
                                                    <th>Name</th>
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
                                                    $titleMap[$game->key] = $game->assets->titles->en;
                                                }
                                            }
                                        @endphp

                                        <table class="datatable table table-stripped">
                                            <thead>
                                                <tr>
                                                    <th>Game</th>
                                                    <th>Level</th>
                                                    <th>Sublevel</th>
                                                    <th>Score</th>
                                                    <th>Time Played (sec)</th>
                                                    <th>Date & Time</th>
                                                    <th>Exit Reason</th>
                                                    <th>Type</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($playedGames as $game)
                                                    <tr>
                                                        <td>
                                                            {{ $titleMap[$game['key']] ?? 'Unknown Game' }}
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
                                                        <td colspan="8" class="text-center">No games played yet.</td>
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
