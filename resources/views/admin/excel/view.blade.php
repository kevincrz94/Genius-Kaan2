@extends('admin.layouts.main')

@section('section')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    @php
                        $excel = session('excel_data', []);
                    @endphp
                    <div class="card customShadow">
                        <form action="{{ route('admin.save.excel') }}" method="post">
                            @csrf
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">
                                    Vista previa de importacion
                                    ({{ count($excel) }})
                                </h5>
                                <div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Guardar
                                    </button>
                                    <a href="{{ url()->current() }}" class="btn btn-secondary ml-2">Cancelar</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:25%">Nombre</th>
                                                <th style="width:25%">Correo</th>
                                                <th style="width:15%">Contrasena</th>
                                                <th style="width:10%">Edad</th>
                                                <th style="width:10%">Genero</th>
                                                <th>Placa</th>
                                                <th>Rango</th>
                                                <th>Unidad</th>
                                                <th>Grupo</th>
                                                <th>Area</th>
                                                <th style="width:15%">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rowsBody">
                                            @forelse($excel as $i => $row)
                                                <tr>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][name]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.name", $row['Nombre'] ?? ($row['Name'] ?? ($row['name'] ?? ''))) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="email" name="rows[{{ $i }}][email]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.email", $row['Correo'] ?? ($row['Email'] ?? ($row['email'] ?? ''))) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][password]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.password", $row['Contrasena'] ?? ($row['Password'] ?? ($row['password'] ?? ''))) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="rows[{{ $i }}][age]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.age", $row['Edad'] ?? ($row['Age'] ?? ($row['age'] ?? ''))) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <select name="rows[{{ $i }}][gender]"
                                                            class="form-control" required>
                                                            <option value="">Selecciona</option>
                                                            <option value="male"
                                                                {{ old("rows.$i.gender", strtolower($row['Genero'] ?? ($row['Gender'] ?? ($row['gender'] ?? '')))) == 'male' ? 'selected' : '' }}>
                                                                Masculino</option>
                                                            <option value="female"
                                                                {{ old("rows.$i.gender", strtolower($row['Genero'] ?? ($row['Gender'] ?? ($row['gender'] ?? '')))) == 'female' ? 'selected' : '' }}>
                                                                Femenino</option>
                                                            <option value="other"
                                                                {{ old("rows.$i.gender", strtolower($row['Genero'] ?? ($row['Gender'] ?? ($row['gender'] ?? '')))) == 'other' ? 'selected' : '' }}>
                                                                Otro</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][badge_number]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.badge_number", $row['Placa'] ?? ($row['Badge Number'] ?? ($row['badge_number'] ?? ''))) }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][rank]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.rank", $row['Rango'] ?? ($row['Rank'] ?? ($row['rank'] ?? ''))) }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][security_unit]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.security_unit", $row['Unidad'] ?? ($row['Security Unit'] ?? ($row['security_unit'] ?? ''))) }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][operational_group]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.operational_group", $row['Grupo Operativo'] ?? ($row['Operational Group'] ?? ($row['operational_group'] ?? ''))) }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][assignment_area]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.assignment_area", $row['Area Asignada'] ?? ($row['Assignment Area'] ?? ($row['assignment_area'] ?? ''))) }}">
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger remove-row">Quitar</button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center py-4">No hay datos disponibles en la sesion.</td>
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
            const addBtn = document.getElementById('addRow');
            const body = document.getElementById('rowsBody');

            function makeRow(index, data) {
                const tr = document.createElement('tr');

                tr.innerHTML = `
                    <td><input type="text" name="rows[${index}][name]" class="form-control" value="${data.name || ''}"></td>
                    <td><input type="email" name="rows[${index}][email]" class="form-control" value="${data.email || ''}"></td>
                    <td><input type="text" name="rows[${index}][password]" class="form-control" value="${data.password || ''}"></td>
                    <td><input type="number" name="rows[${index}][age]" class="form-control" value="${data.age || ''}"></td>
                    <td>
                        <select name="rows[${index}][gender]" class="form-control">
                            <option value="">Selecciona</option>
                            <option value="male">Masculino</option>
                            <option value="female">Femenino</option>
                            <option value="other">Otro</option>
                        </select>
                    </td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row">Quitar</button></td>
                `;

                return tr;
            }

            addBtn && addBtn.addEventListener('click', function() {
                // compute next index
                const index = body.querySelectorAll('tr').length;
                const row = makeRow(index, {});
                body.appendChild(row);
            });

            body.addEventListener('click', function(e) {
                if (e.target && e.target.matches('.remove-row')) {
                    const tr = e.target.closest('tr');
                    tr && tr.remove();
                    // reindex inputs
                    Array.from(body.querySelectorAll('tr')).forEach((r, idx) => {
                        Array.from(r.querySelectorAll('input, select')).forEach(inp => {
                            const name = inp.getAttribute('name');
                            if (!name) return;
                            const newName = name.replace(/rows\[\d+\]/, `rows[${idx}]`);
                            inp.setAttribute('name', newName);
                        });
                    });
                }
            });
        })();
    </script>
@endsection
