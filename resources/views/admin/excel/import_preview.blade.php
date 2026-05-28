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
                        <form action="{{ route('admin.save.excel') }}" method="post" id="importForm">
                            @csrf
                            <div class="card-header d-flex justify-content-between align-items-center bg-white pt-4 pb-3">
                                <div>
                                    <h4 class="card-title text-primary mb-1">
                                        <i class="fe fe-users me-2"></i>
                                        Auditoría de importación ({{ count($excel) }})
                                    </h4>
                                    <p class="text-muted small mb-0">
                                        Verifique el cruce de datos con los catálogos institucionales antes de consolidar el registro.
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('admin.user.management') }}" class="btn btn-outline-secondary">
                                        Cancelar
                                    </a>
                                    <button type="submit" class="btn btn-primary" id="submitImport">
                                        <i class="fa-solid fa-floppy-disk me-1"></i>
                                        Consolidar alta
                                    </button>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive import-preview-table">
                                    <table class="table table-hover table-striped mb-0 align-middle text-nowrap">
                                        <thead class="table-light import-preview-head">
                                            <tr>
                                                <th>Nombre del elemento</th>
                                                <th>Correo institucional</th>
                                                <th>Credencial</th>
                                                <th>Edad</th>
                                                <th>Género</th>
                                                <th>Placa / ID</th>
                                                <th>Rango operativo</th>
                                                <th>Unidad</th>
                                                <th>Grupo</th>
                                                <th>Área</th>
                                                <th class="text-center">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rowsBody">
                                            @forelse($excel as $i => $row)
                                                @php
                                                    $rankName = old("rows.$i.rank", $pick($row, ['Rango', 'Rank', 'rank']));
                                                    $unitName = old("rows.$i.security_unit", $pick($row, ['Unidad', 'Security Unit', 'security_unit']));
                                                    $groupName = old("rows.$i.operational_group", $pick($row, ['Grupo Operativo', 'Operational Group', 'operational_group']));
                                                    $areaName = old("rows.$i.assignment_area", $pick($row, ['Área Asignada', 'Area Asignada', 'Assignment Area', 'assignment_area']));

                                                    $rankMatch = $ranks->firstWhere('name', $rankName);
                                                    $unitMatch = $units->firstWhere('name', $unitName);
                                                    $groupMatch = $groups->firstWhere('name', $groupName);
                                                    $areaMatch = $areas->firstWhere('name', $areaName);
                                                    $genderValue = old("rows.$i.gender", strtolower($pick($row, ['Género', 'Genero', 'Gender', 'gender'])));
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][name]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("rows.$i.name", $pick($row, ['Nombre', 'Name', 'name'])) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="email" name="rows[{{ $i }}][email]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("rows.$i.email", $pick($row, ['Correo', 'Email', 'email'])) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="password" name="rows[{{ $i }}][password]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("rows.$i.password", $pick($row, ['Contraseña', 'Contrasena', 'Password', 'password'])) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="rows[{{ $i }}][age]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("rows.$i.age", $pick($row, ['Edad', 'Age', 'age'])) }}">
                                                    </td>
                                                    <td>
                                                        <select name="rows[{{ $i }}][gender]" class="form-select form-select-sm">
                                                            <option value="">Seleccione</option>
                                                            <option value="male" @selected(in_array($genderValue, ['male', 'masculino'], true))>Masculino</option>
                                                            <option value="female" @selected(in_array($genderValue, ['female', 'femenino'], true))>Femenino</option>
                                                            <option value="other" @selected(in_array($genderValue, ['other', 'otro'], true))>Otro</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][badge_number]"
                                                            class="form-control form-control-sm"
                                                            value="{{ old("rows.$i.badge_number", $pick($row, ['Placa', 'Badge Number', 'badge_number'])) }}">
                                                    </td>
                                                    <td>
                                                        <select name="rows[{{ $i }}][rank_id]"
                                                            class="form-select form-select-sm {{ ! $rankMatch && $rankName ? 'border-danger' : '' }}">
                                                            <option value="">Seleccione rango</option>
                                                            @foreach ($ranks as $rank)
                                                                <option value="{{ $rank->id }}" @selected($rankMatch && $rankMatch->id === $rank->id)>
                                                                    {{ $rank->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if (! $rankMatch && $rankName)
                                                            <input type="hidden" name="rows[{{ $i }}][rank]" value="{{ $rankName }}">
                                                            <small class="text-danger">Excel: {{ $rankName }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <select name="rows[{{ $i }}][security_unit_id]"
                                                            class="form-select form-select-sm {{ ! $unitMatch && $unitName ? 'border-danger' : '' }}">
                                                            <option value="">Seleccione unidad</option>
                                                            @foreach ($units as $unit)
                                                                <option value="{{ $unit->id }}" @selected($unitMatch && $unitMatch->id === $unit->id)>
                                                                    {{ $unit->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if (! $unitMatch && $unitName)
                                                            <input type="hidden" name="rows[{{ $i }}][security_unit]" value="{{ $unitName }}">
                                                            <small class="text-danger">Excel: {{ $unitName }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <select name="rows[{{ $i }}][operational_group_id]"
                                                            class="form-select form-select-sm {{ ! $groupMatch && $groupName ? 'border-danger' : '' }}">
                                                            <option value="">Seleccione grupo</option>
                                                            @foreach ($groups as $group)
                                                                <option value="{{ $group->id }}" @selected($groupMatch && $groupMatch->id === $group->id)>
                                                                    {{ $group->name }}{{ $group->unit ? ' / '.$group->unit->name : '' }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if (! $groupMatch && $groupName)
                                                            <input type="hidden" name="rows[{{ $i }}][operational_group]" value="{{ $groupName }}">
                                                            <small class="text-danger">Excel: {{ $groupName }}</small>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <select name="rows[{{ $i }}][assignment_area_id]"
                                                            class="form-select form-select-sm {{ ! $areaMatch && $areaName ? 'border-danger' : '' }}">
                                                            <option value="">Seleccione área</option>
                                                            @foreach ($areas as $area)
                                                                <option value="{{ $area->id }}" @selected($areaMatch && $areaMatch->id === $area->id)>
                                                                    {{ $area->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if (! $areaMatch && $areaName)
                                                            <input type="hidden" name="rows[{{ $i }}][assignment_area]" value="{{ $areaName }}">
                                                            <small class="text-danger">Excel: {{ $areaName }}</small>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row"
                                                            title="Descartar elemento">
                                                            <i class="fa fa-trash pointer-events-none"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center py-5 text-muted">
                                                        <i class="fe fe-file-minus fs-1 d-block mb-2"></i>
                                                        No hay datos estructurados en la sesión actual.
                                                    </td>
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
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.getElementById('rowsBody');
            const form = document.getElementById('importForm');
            const submitButton = document.getElementById('submitImport');

            body && body.addEventListener('click', function(e) {
                const removeButton = e.target.closest('.remove-row');

                if (!removeButton) {
                    return;
                }

                const row = removeButton.closest('tr');
                row && row.remove();

                Array.from(body.querySelectorAll('tr')).forEach((currentRow, idx) => {
                    Array.from(currentRow.querySelectorAll('input, select')).forEach((input) => {
                        const name = input.getAttribute('name');
                        if (!name) {
                            return;
                        }

                        input.setAttribute('name', name.replace(/rows\[\d+\]/, `rows[${idx}]`));
                    });
                });
            });

            form && form.addEventListener('submit', function() {
                if (!submitButton) {
                    return;
                }

                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Procesando...';
            });
        });
    </script>
@endpush
