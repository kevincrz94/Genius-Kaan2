@extends('admin.layouts.main')

@section('section')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h3 class="mb-1 text-primary">
                        <i class="fe fe-layers me-2"></i>
                        Catálogos operativos
                    </h3>
                    <p class="text-muted mb-0">
                        Administración de diccionarios de datos para altas individuales e importaciones masivas.
                    </p>
                </div>
                <a href="{{ route('admin.user.management.add') }}" class="btn btn-primary shadow-sm">
                    <i class="fa fa-user-plus me-1"></i>
                    Agregar elemento
                </a>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card customShadow h-100 border-top border-primary border-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fe fe-star text-primary me-2"></i>
                                Rangos y cargos
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('admin.catalogs.ranks.store') }}" class="row g-2 mb-4">
                                @csrf
                                <div class="col-md-6">
                                    <input name="name" class="form-control bg-light" placeholder="Ej. Policía primero" required>
                                </div>
                                <div class="col-md-4">
                                    <input name="code" class="form-control bg-light" placeholder="Clave interna">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100" title="Guardar rango">
                                        <i class="fe fe-plus"></i>
                                    </button>
                                </div>
                            </form>
                            <div class="table-responsive catalog-table-scroll">
                                <table class="table table-hover table-sm mb-0 align-middle">
                                    <thead class="table-light catalog-table-head">
                                        <tr>
                                            <th>Nombre del rango</th>
                                            <th class="catalog-code-col">Clave</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($ranks as $rank)
                                            <tr>
                                                <td class="fw-medium">{{ $rank->name }}</td>
                                                <td>
                                                    <span class="badge bg-light text-secondary border font-monospace">
                                                        {{ $rank->code ?: 'N/A' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-muted text-center py-4">
                                                    <i class="fe fe-info me-1"></i>
                                                    Sin rangos registrados.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card customShadow h-100 border-top border-info border-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fe fe-shield text-info me-2"></i>
                                Unidades operativas
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('admin.catalogs.units.store') }}" class="row g-2 mb-4">
                                @csrf
                                <div class="col-md-4">
                                    <input name="name" class="form-control bg-light" placeholder="Ej. Policía Municipal" required>
                                </div>
                                <div class="col-md-3">
                                    <input name="type" class="form-control bg-light" placeholder="Tipo">
                                </div>
                                <div class="col-md-3">
                                    <input name="code" class="form-control bg-light" placeholder="Clave">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-info text-white w-100" title="Guardar unidad">
                                        <i class="fe fe-plus"></i>
                                    </button>
                                </div>
                            </form>
                            <div class="table-responsive catalog-table-scroll">
                                <table class="table table-hover table-sm mb-0 align-middle">
                                    <thead class="table-light catalog-table-head">
                                        <tr>
                                            <th>Unidad</th>
                                            <th>Tipo</th>
                                            <th class="catalog-code-col-sm">Clave</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($units as $unit)
                                            <tr>
                                                <td class="fw-medium">{{ $unit->name }}</td>
                                                <td class="text-muted small">{{ $unit->type ?: '-' }}</td>
                                                <td>
                                                    <span class="badge bg-light text-secondary border font-monospace">
                                                        {{ $unit->code ?: 'N/A' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-muted text-center py-4">
                                                    <i class="fe fe-info me-1"></i>
                                                    Sin unidades registradas.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-4">
                    <div class="card customShadow h-100 border-top border-warning border-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fe fe-users text-warning me-2"></i>
                                Grupos tácticos / turnos
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('admin.catalogs.groups.store') }}" class="row g-2 mb-4">
                                @csrf
                                <div class="col-md-3">
                                    <select name="security_unit_id" class="form-select bg-light">
                                        <option value="">Sin unidad</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input name="name" class="form-control bg-light" placeholder="Grupo" required>
                                </div>
                                <div class="col-md-2">
                                    <input name="shift" class="form-control bg-light" placeholder="Turno">
                                </div>
                                <div class="col-md-2">
                                    <input name="code" class="form-control bg-light" placeholder="Clave">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-warning text-dark w-100 fw-bold" title="Guardar grupo">
                                        <i class="fe fe-plus"></i>
                                    </button>
                                </div>
                            </form>
                            <div class="table-responsive catalog-table-scroll">
                                <table class="table table-hover table-sm mb-0 align-middle">
                                    <thead class="table-light catalog-table-head">
                                        <tr>
                                            <th>Grupo</th>
                                            <th>Unidad superior</th>
                                            <th class="text-center">Turno</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($groups as $group)
                                            <tr>
                                                <td class="fw-medium">{{ $group->name }}</td>
                                                <td class="text-muted small">{{ $group->unit?->name ?: 'N/A' }}</td>
                                                <td class="text-center">
                                                    @if ($group->shift)
                                                        <span class="badge bg-secondary">{{ $group->shift }}</span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-muted text-center py-4">
                                                    <i class="fe fe-info me-1"></i>
                                                    Sin grupos registrados.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-4">
                    <div class="card customShadow h-100 border-top border-success border-3">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">
                                <i class="fe fe-map-pin text-success me-2"></i>
                                Áreas de despliegue
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('admin.catalogs.areas.store') }}" class="row g-2 mb-4">
                                @csrf
                                <div class="col-md-6">
                                    <input name="name" class="form-control bg-light" placeholder="Ej. Tránsito / Vialidad" required>
                                </div>
                                <div class="col-md-4">
                                    <input name="code" class="form-control bg-light" placeholder="Sector / Clave">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-success text-white w-100" title="Guardar área">
                                        <i class="fe fe-plus"></i>
                                    </button>
                                </div>
                            </form>
                            <div class="table-responsive catalog-table-scroll">
                                <table class="table table-hover table-sm mb-0 align-middle">
                                    <thead class="table-light catalog-table-head">
                                        <tr>
                                            <th>Área geográfica / operativa</th>
                                            <th class="catalog-code-col">Clave</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($areas as $area)
                                            <tr>
                                                <td class="fw-medium">{{ $area->name }}</td>
                                                <td>
                                                    <span class="badge bg-light text-secondary border font-monospace">
                                                        {{ $area->code ?: 'N/A' }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-muted text-center py-4">
                                                    <i class="fe fe-info me-1"></i>
                                                    Sin áreas registradas.
                                                </td>
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
@endsection
