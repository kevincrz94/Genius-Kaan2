@php
    $isEdit = filled($user);
    $statusValue = old('status', $user['status'] ?? 1);
    $roleValue = old('role', $user['role'] ?? 'user');
    $genderValue = old('gender', $user['gender'] ?? '');
@endphp

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">{{ $title }}</h5>
                    <p class="text-muted small mb-0">Los campos obligatorios son nombre, correo, perfil y contraseña en altas nuevas.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form action="{{ $action }}" method="post" enctype="multipart/form-data">
                @csrf
                @if ($method !== 'POST')
                    @method($method)
                @endif

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-lg-4">
                            <label class="form-label">Nombre completo</label>
                            <input name="name" type="text" class="form-control"
                                value="{{ old('name', $user['name'] ?? '') }}" required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Correo institucional</label>
                            <input name="email" type="email" class="form-control"
                                value="{{ old('email', $user['email'] ?? '') }}" required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Perfil</label>
                            <select name="role" class="form-select" required>
                                @foreach ($roleLabels as $role => $label)
                                    <option value="{{ $role }}" @selected($roleValue === $role)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Contraseña {{ $isEdit ? '(opcional)' : '' }}</label>
                            <input name="password" type="password" class="form-control" autocomplete="new-password"
                                @required(! $isEdit)>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Confirmar contraseña</label>
                            <input name="confirm_password" type="password" class="form-control" autocomplete="new-password">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Estado</label>
                            <select name="status" class="form-select" required>
                                <option value="1" @selected((string) $statusValue === '1')>Activo</option>
                                <option value="0" @selected((string) $statusValue === '0')>Inactivo</option>
                                <option value="2" @selected((string) $statusValue === '2')>Suspendido</option>
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Fotografía</label>
                            <input name="image" type="file" class="form-control" accept="image/*">
                        </div>

                        <div class="col-lg-3">
                            <label class="form-label">Placa / ID</label>
                            <input name="badge_number" type="text" class="form-control"
                                value="{{ old('badge_number', $user['badge_number'] ?? '') }}">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Edad</label>
                            <input name="age" type="number" min="1" max="120" class="form-control"
                                value="{{ old('age', $user['age'] ?? '') }}">
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Género</label>
                            <select name="gender" class="form-select">
                                <option value="">Seleccione</option>
                                @foreach ($genderLabels as $gender => $label)
                                    <option value="{{ $gender }}" @selected($genderValue === $gender)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <label class="form-label">Rango / cargo</label>
                            <select name="rank_id" class="form-select">
                                <option value="">Sin rango</option>
                                @foreach ($ranks as $rank)
                                    <option value="{{ $rank->id }}" @selected((string) old('rank_id', $user['rank_id'] ?? '') === (string) $rank->id)>
                                        {{ $rank->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-4">
                            <label class="form-label">Unidad</label>
                            <select name="security_unit_id" class="form-select">
                                <option value="">Sin unidad</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}" @selected((string) old('security_unit_id', $user['security_unit_id'] ?? '') === (string) $unit->id)>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Grupo operativo</label>
                            <select name="operational_group_id" class="form-select">
                                <option value="">Sin grupo</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}" @selected((string) old('operational_group_id', $user['operational_group_id'] ?? '') === (string) $group->id)>
                                        {{ $group->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label">Área asignada</label>
                            <select name="assignment_area_id" class="form-select">
                                <option value="">Sin área</option>
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}" @selected((string) old('assignment_area_id', $user['assignment_area_id'] ?? '') === (string) $area->id)>
                                        {{ $area->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-1"></i>
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
