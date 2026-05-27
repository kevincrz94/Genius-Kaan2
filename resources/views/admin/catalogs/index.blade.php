@extends('admin.layouts.main')

@section('section')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h3 class="mb-1">Catálogos operativos</h3>
                    <p class="text-muted mb-0">Administra los valores disponibles para alta individual e importación masiva.</p>
                </div>
                <a href="{{ route('admin.user.management.add') }}" class="btn btn-primary">
                    <i class="fa fa-user-plus"></i>
                    Agregar elemento
                </a>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card customShadow h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Rangos / cargos</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('admin.catalogs.ranks.store') }}" class="row g-2 mb-3">
                                @csrf
                                <div class="col-md-7">
                                    <input name="name" class="form-control" placeholder="Ej. Policía primero" required>
                                </div>
                                <div class="col-md-3">
                                    <input name="code" class="form-control" placeholder="Clave">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100">Crear</button>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Clave</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($ranks as $rank)
                                            <tr>
                                                <td>{{ $rank->name }}</td>
                                                <td>{{ $rank->code ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-muted text-center py-3">Sin rangos registrados.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card customShadow h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Unidades</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('admin.catalogs.units.store') }}" class="row g-2 mb-3">
                                @csrf
                                <div class="col-md-5">
                                    <input name="name" class="form-control" placeholder="Ej. Policía Municipal" required>
                                </div>
                                <div class="col-md-3">
                                    <input name="type" class="form-control" placeholder="Tipo">
                                </div>
                                <div class="col-md-2">
                                    <input name="code" class="form-control" placeholder="Clave">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100">Crear</button>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Tipo</th>
                                            <th>Clave</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($units as $unit)
                                            <tr>
                                                <td>{{ $unit->name }}</td>
                                                <td>{{ $unit->type ?: '-' }}</td>
                                                <td>{{ $unit->code ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-muted text-center py-3">Sin unidades registradas.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-lg-6">
                    <div class="card customShadow h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Grupos operativos</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('admin.catalogs.groups.store') }}" class="row g-2 mb-3">
                                @csrf
                                <div class="col-md-4">
                                    <select name="security_unit_id" class="form-control">
                                        <option value="">Sin unidad</option>
                                        @foreach ($units as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input name="name" class="form-control" placeholder="Grupo / turno" required>
                                </div>
                                <div class="col-md-2">
                                    <input name="shift" class="form-control" placeholder="Turno">
                                </div>
                                <div class="col-md-2">
                                    <input name="code" class="form-control" placeholder="Clave">
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-primary w-100">+</button>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Grupo</th>
                                            <th>Unidad</th>
                                            <th>Turno</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($groups as $group)
                                            <tr>
                                                <td>{{ $group->name }}</td>
                                                <td>{{ $group->unit?->name ?: '-' }}</td>
                                                <td>{{ $group->shift ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-muted text-center py-3">Sin grupos registrados.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card customShadow h-100">
                        <div class="card-header">
                            <h5 class="mb-0">Áreas asignadas</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="{{ route('admin.catalogs.areas.store') }}" class="row g-2 mb-3">
                                @csrf
                                <div class="col-md-7">
                                    <input name="name" class="form-control" placeholder="Ej. Tránsito" required>
                                </div>
                                <div class="col-md-3">
                                    <input name="code" class="form-control" placeholder="Clave">
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-primary w-100">Crear</button>
                                </div>
                            </form>
                            <div class="table-responsive">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>Nombre</th>
                                            <th>Clave</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($areas as $area)
                                            <tr>
                                                <td>{{ $area->name }}</td>
                                                <td>{{ $area->code ?: '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-muted text-center py-3">Sin áreas registradas.</td>
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
