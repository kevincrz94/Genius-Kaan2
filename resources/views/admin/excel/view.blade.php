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
                                    Import Preview
                                    ({{ count($excel) }})
                                </h5>
                                <div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fa-solid fa-floppy-disk"></i>
                                        Save
                                    </button>
                                    <a href="{{ url()->current() }}" class="btn btn-secondary ml-2">Cancel</a>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width:25%">Name</th>
                                                <th style="width:25%">Email</th>
                                                <th style="width:15%">Password</th>
                                                <th style="width:10%">Age</th>
                                                <th style="width:10%">Gender</th>
                                                <th>Placa</th>
                                                <th>Rango</th>
                                                <th>Unidad</th>
                                                <th>Grupo</th>
                                                <th>Area</th>
                                                <th style="width:15%">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody id="rowsBody">
                                            @forelse($excel as $i => $row)
                                                <tr>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][name]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.name", $row['Name'] ?? ($row['name'] ?? '')) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="email" name="rows[{{ $i }}][email]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.email", $row['Email'] ?? ($row['email'] ?? '')) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][password]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.password", $row['Password'] ?? ($row['password'] ?? '')) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="number" name="rows[{{ $i }}][age]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.age", $row['Age'] ?? ($row['age'] ?? '')) }}"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <select name="rows[{{ $i }}][gender]"
                                                            class="form-control" required>
                                                            <option value="">Select</option>
                                                            <option value="male"
                                                                {{ old("rows.$i.gender", strtolower($row['Gender'] ?? ($row['gender'] ?? ''))) == 'male' ? 'selected' : '' }}>
                                                                Male</option>
                                                            <option value="female"
                                                                {{ old("rows.$i.gender", strtolower($row['Gender'] ?? ($row['gender'] ?? ''))) == 'female' ? 'selected' : '' }}>
                                                                Female</option>
                                                            <option value="other"
                                                                {{ old("rows.$i.gender", strtolower($row['Gender'] ?? ($row['gender'] ?? ''))) == 'other' ? 'selected' : '' }}>
                                                                Other</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][badge_number]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.badge_number", $row['Badge Number'] ?? ($row['badge_number'] ?? '')) }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][rank]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.rank", $row['Rank'] ?? ($row['rank'] ?? '')) }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][security_unit]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.security_unit", $row['Security Unit'] ?? ($row['security_unit'] ?? '')) }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][operational_group]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.operational_group", $row['Operational Group'] ?? ($row['operational_group'] ?? '')) }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="rows[{{ $i }}][assignment_area]"
                                                            class="form-control"
                                                            value="{{ old("rows.$i.assignment_area", $row['Assignment Area'] ?? ($row['assignment_area'] ?? '')) }}">
                                                    </td>
                                                    <td>
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger remove-row">Remove</button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="11" class="text-center py-4">No data available in
                                                        session.</td>
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
                            <option value="">Select</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </td>
                    <td><button type="button" class="btn btn-sm btn-danger remove-row">Remove</button></td>
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
