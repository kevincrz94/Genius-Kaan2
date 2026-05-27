@extends('admin.layouts.main')

@section('section')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card customShadow">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-1">Agregar elemento</h4>
                                    <p class="text-muted mb-0">El token Cognifit se genera desde este flujo administrativo.</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.catalogs.index') }}" class="btn btn-outline-primary">
                                        <i class="fa fa-list"></i>
                                        Catálogos
                                    </a>
                                    <a href="{{ route('admin.user.management') }}" class="btn btn-primary">
                                        <i class="fa fa-arrow-left"></i>
                                        Volver
                                    </a>
                                </div>
                            </div>
                        </div>
                        <form action="{{ route('admin.users.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4 form-group">
                                        <label>Nombre</label>
                                        <input type="text" class="form-control" placeholder="Nombre" name="name"
                                            value="{{ old('name') }}" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Correo</label>
                                        <input type="email" class="form-control" placeholder="Correo" name="email"
                                            value="{{ old('email') }}" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Edad</label>
                                        <input type="number" class="form-control" placeholder="Edad" name="age"
                                            value="{{ old('age') }}">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Placa / ID operativo</label>
                                        <input type="text" class="form-control" placeholder="Placa" name="badge_number"
                                            value="{{ old('badge_number') }}">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Rango / cargo</label>
                                        <select name="rank_id" class="form-control">
                                            <option value="">Selecciona rango</option>
                                            @foreach ($ranks as $rank)
                                                <option value="{{ $rank->id }}" @selected(old('rank_id') == $rank->id)>
                                                    {{ $rank->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Unidad</label>
                                        <select name="security_unit_id" class="form-control">
                                            <option value="">Selecciona unidad</option>
                                            @foreach ($units as $unit)
                                                <option value="{{ $unit->id }}" @selected(old('security_unit_id') == $unit->id)>
                                                    {{ $unit->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Grupo operativo</label>
                                        <select name="operational_group_id" class="form-control">
                                            <option value="">Selecciona grupo</option>
                                            @foreach ($groups as $group)
                                                <option value="{{ $group->id }}" @selected(old('operational_group_id') == $group->id)>
                                                    {{ $group->name }}{{ $group->unit ? ' / '.$group->unit->name : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Área asignada</label>
                                        <select name="assignment_area_id" class="form-control">
                                            <option value="">Selecciona área</option>
                                            @foreach ($areas as $area)
                                                <option value="{{ $area->id }}" @selected(old('assignment_area_id') == $area->id)>
                                                    {{ $area->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Contraseña</label>
                                        <input type="password" class="form-control" placeholder="Contraseña" name="password"
                                            required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Confirmar contraseña</label>
                                        <input type="password" class="form-control" placeholder="Confirmar contraseña"
                                            name="confirm_password">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label>Género</label>
                                        <select name="gender" class="form-control">
                                            <option selected disabled>Selecciona género</option>
                                            <option value="male">Masculino</option>
                                            <option value="female">Femenino</option>
                                            <option value="other">Otro</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-12 form-group">
                                        <label>Imagen</label>
                                        <input type="file" class="form-control" name="image" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
