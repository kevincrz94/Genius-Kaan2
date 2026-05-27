@extends('admin.layouts.main')

@section('section')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <!-- LEFT SIDE USERS LIST -->
                <div class="col-lg-12">
                    <div class="card customShadow">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="card-title mb-1">Agregar elemento</h4>
                                    <p class="text-muted mb-0">El token Cognifit se genera desde este flujo administrativo.</p>
                                </div>
                                <a href="{{ route('admin.user.management') }}" class="btn btn-primary">
                                    <i class="fa fa-arrow-left"></i>
                                    Volver
                                </a>
                            </div>
                        </div>
                        <form action="{{ route('admin.users.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4 form-group">
                                        <label for="">Nombre</label>
                                        <input type="text" class="form-control" placeholder="Nombre" name="name"
                                            value="{{ old('name') }}" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Correo</label>
                                        <input type="email" class="form-control" placeholder="Correo" name="email"
                                            value="{{ old('email') }}" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Edad</label>
                                        <input type="number" class="form-control" placeholder="Edad" name="age"
                                            value="{{ old('age') }}" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Placa / ID operativo</label>
                                        <input type="text" class="form-control" placeholder="Placa" name="badge_number"
                                            value="{{ old('badge_number') }}">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Rango / cargo</label>
                                        <input type="text" class="form-control" placeholder="Rango" name="rank"
                                            value="{{ old('rank') }}">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Unidad</label>
                                        <input type="text" class="form-control" placeholder="Policia Municipal"
                                            name="security_unit_name" value="{{ old('security_unit_name') }}">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Grupo operativo</label>
                                        <input type="text" class="form-control" placeholder="Turno A / Patrullaje"
                                            name="operational_group_name" value="{{ old('operational_group_name') }}">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Area asignada</label>
                                        <input type="text" class="form-control" placeholder="Transito, proximidad, reaccion"
                                            name="assignment_area" value="{{ old('assignment_area') }}">
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Contrasena</label>
                                        <input type="password" class="form-control" placeholder="Contrasena" name="password"
                                            required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Confirmar contrasena</label>
                                        <input type="password" class="form-control" placeholder="Confirmar contrasena"
                                            name="confirm_password" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Genero</label>
                                        <select name="gender" class="form-control" required>
                                            <option selected disabled>Selecciona genero</option>
                                            <option value="male">Masculino</option>
                                            <option value="female">Femenino</option>
                                            <option value="other">Otro</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-12 form-group">
                                        <label for="">Imagen</label>
                                        <input type="file" class="form-control" name="image" accept="image/*">
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        Guardar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
