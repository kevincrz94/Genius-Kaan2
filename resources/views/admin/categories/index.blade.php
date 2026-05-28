@extends('admin.layouts.main')

@section('section')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h3 class="mb-1 text-primary">
                        <i class="fa-solid fa-brain me-2"></i>
                        Catálogo de Capacidades Cognitivas
                    </h3>
                    <p class="text-muted mb-0">Métricas de evaluación disponibles a través del motor de CogniFit.</p>
                </div>
                <div>
                    <span class="badge bg-primary fs-6 py-2 px-3 shadow-sm">
                        Total activas: {{ $list->count() }}
                    </span>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <div class="card customShadow border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="datatable table table-hover table-striped mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center capability-index-col">#</th>
                                            <th class="text-center capability-icon-col">Ícono</th>
                                            <th>Capacidad evaluada</th>
                                            <th>Clave de sistema</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($list as $item)
                                            <tr>
                                                <td class="text-center text-muted fw-bold">{{ $loop->iteration }}</td>
                                                <td class="text-center">
                                                    <div class="avatar avatar-sm d-inline-block">
                                                        <img class="avatar-img rounded-circle shadow-sm capability-icon"
                                                            src="{{ $item->assets->images->whiteIcon }}"
                                                            alt="Ícono de {{ $item->key }}">
                                                    </div>
                                                </td>
                                                <td class="fw-bold text-dark">
                                                    {{ $item->assets->titles->es ?? $item->assets->titles->en }}
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-secondary border font-monospace">
                                                        {{ $item->key }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
