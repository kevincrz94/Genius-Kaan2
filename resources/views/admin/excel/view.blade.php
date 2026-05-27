@extends('admin.layouts.main')

@section('section')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    @php
                        $excel = session('excel_data', []);
                        $pick = function (array $row, array $keys, string $default = '') {
                            foreach ($keys as $key) {
                                if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                                    return trim((string) $row[$key]);
                                }
                            }

                            return $default;
                        };
                    @endphp
                    <div class="card customShadow">
                        <form action="{{ route('admin.save.excel') }}" method="post">
                            @csrf
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1">Vista previa de importación ({{ count($excel) }})</h5>
                                    <p class="text-muted mb-0">Selecciona rango, unidad, grupo y área antes de guardar.</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.catalogs.index') }}" class="btn btn-outline-primary">
                                        <i class="fa fa-list"></i>
                                        Catálogos
                                    </a>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Guardar
                                    </button>
                                    <a href="{{ route('admin.user.management') }}" class="btn btn-secondary">Cancelar</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0 align-middle">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Correo</th>
                                                <th>Contraseña</th>
                                                <th>Edad</th>
                                                <th>Género</th>
                                                <th>Placa</th>
                                                <th>Rango</th>
                                                <th>Unidad</th>
                                                <th>Grupo</th>
                                                <th>Área</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rowsBody">
                                            @forelse($excel as $i => $row)
                                                @php
                                                    $rankValue = old("rows.$i.rank", $pick($row, ['Rango', 'Rank', 'rank']));
                                                    $unitValue = old("rows.$i.security_unit", $pick($row, ['Unidad', 'Security Unit', 'security_unit']));
                                                    $groupValue = old("rows.$i.operational_group", $pick($row, ['Grupo Operativo', 'Operational Group', 'operational_group']));
                                                    $areaValue = old("rows.$i.assignment_area", $pick($row, ['Área Asignada', 'Area Asignada', 'Assignment Area', 'assignment_area']));
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][name]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.name", $pick($row, ['Nombre', 'Name', 'name'])) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="email" name="rows[{{ $i }}][email]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.email", $pick($row, ['Correo', 'Email', 'email'])) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][password]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.password", $pick($row, ['Contraseña', 'Contrasena', 'Password', 'password'])) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="rows[{{ $i }}][age]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.age", $pick($row, ['Edad', 'Age', 'age'])) }}">
                                                    </td>
                                                    <td>
                                                        @php
                                                            $genderValue = old("rows.$i.gender", strtolower($pick($row, ['Género', 'Genero', 'Gender', 'gender'])));
                                                        @endphp
                                                        <select name="rows[{{ $i }}][gender]" class="form-control">
                                                            <option value="">Selecciona</option>
                                                            <option value="male" @selected($genderValue === 'male')>Masculino</option>
                                                            <option value="female" @selected($genderValue === 'female')>Femenino</option>
                                                            <option value="other" @selected($genderValue === 'other')>Otro</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][badge_number]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.badge_number", $pick($row, ['Placa', 'Badge Number', 'badge_number'])) }}">
                                                    </td>
                                                    <td>
                                                        <select name="rows[{{ $i }}][rank]" class="form-control">
                                                            <option value="">Selecciona</option>
                                                            @if ($rankValue && ! $ranks->contains('name', $rankValue))
                                                                <option value="{{ $rankValue }}" selected>{{ $rankValue }}</option>
                                                            @endif
                                                            @foreach ($ranks as $rank)
                                                                <option value="{{ $rank->name }}" @selected($rankValue === $rank->name)>
                                                                    {{ $rank->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="rows[{{ $i }}][security_unit]" class="form-control">
                                                            <option value="">Selecciona</option>
                                                            @if ($unitValue && ! $units->contains('name', $unitValue))
                                                                <option value="{{ $unitValue }}" selected>{{ $unitValue }}</option>
                                                            @endif
                                                            @foreach ($units as $unit)
                                                                <option value="{{ $unit->name }}" @selected($unitValue === $unit->name)>
                                                                    {{ $unit->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="rows[{{ $i }}][operational_group]" class="form-control">
                                                            <option value="">Selecciona</option>
                                                            @if ($groupValue && ! $groups->contains('name', $groupValue))
                                                                <option value="{{ $groupValue }}" selected>{{ $groupValue }}</option>
                                                            @endif
                                                            @foreach ($groups as $group)
                                                                <option value="{{ $group->name }}" @selected($groupValue === $group->name)>
                                                                    {{ $group->name }}{{ $group->unit ? ' / '.$group->unit->name : '' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="rows[{{ $i }}][assignment_area]" class="form-control">
                                                            <option value="">Selecciona</option>
                                                            @if ($areaValue && ! $areas->contains('name', $areaValue))
                                                                <option value="{{ $areaValue }}" selected>{{ $areaValue }}</option>
                                                            @endif
                                                            @foreach ($areas as $area)
                                                                <option value="{{ $area->name }}" @selected($areaValue === $area->name)>
                                                                    {{ $area->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-danger remove-row">Quitar</button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center py-4">No hay datos disponibles en la sesión.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const body = document.getElementById('rowsBody');

            body && body.addEventListener('click', function(e) {
                if (e.target && e.target.matches('.remove-row')) {
                    const tr = e.target.closest('tr');
                    tr && tr.remove();

                    Array.from(body.querySelectorAll('tr')).forEach((r, idx) => {
                        Array.from(r.querySelectorAll('input, select')).forEach(inp => {
                            const name = inp.getAttribute('name');
                            if (!name) return;
                            inp.setAttribute('name', name.replace(/rows\[\d+\]/, `rows[${idx}]`));
                        });
                    });
                }
            });
        })();
    </script>
@endsection
