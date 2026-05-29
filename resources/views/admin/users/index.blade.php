@extends('admin.layouts.main')

@section('section')
    @php
        $roleLabels = [
            'user' => 'Usuario operativo',
            'admin' => 'Administrador',
            'super_admin' => 'Superusuario',
        ];
        $statusLabels = [
            0 => ['label' => 'Inactivo', 'class' => 'bg-warning text-dark'],
            1 => ['label' => 'Activo', 'class' => 'bg-success'],
            2 => ['label' => 'Suspendido', 'class' => 'bg-danger'],
        ];
        $genderLabels = [
            'male' => 'Masculino',
            'female' => 'Femenino',
            'other' => 'Otro',
        ];
    @endphp

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <div>
                    <h3 class="mb-1 text-primary"><i class="fa fa-users me-2"></i>Gestión de elementos</h3>
                    <p class="text-muted mb-0">Alta, edición, filtros e importación masiva de personal institucional.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.download.excel') }}" class="btn btn-outline-primary">
                        <i class="fa-solid fa-file-excel me-1"></i>
                        Plantilla
                    </a>
                    <button id="exportUsersTable" class="btn btn-outline-success" type="button">
                        <i class="fa fa-download me-1"></i>
                        Exportar tabla
                    </button>
                    <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#importModal">
                        <i class="fa fa-upload me-1"></i>
                        Importar
                    </button>
                    <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#createUserModal">
                        <i class="fa fa-user-plus me-1"></i>
                        Nuevo elemento
                    </button>
                </div>
            </div>

            <div class="card customShadow mb-4">
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-3">
                            <label for="userSearch" class="form-label text-muted small text-uppercase fw-bold">Búsqueda</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa fa-search"></i></span>
                                <input id="userSearch" type="search" class="form-control" placeholder="Nombre, correo, placa o rango">
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label for="roleFilter" class="form-label text-muted small text-uppercase fw-bold">Perfil</label>
                            <select id="roleFilter" class="form-select table-filter" data-column="4">
                                <option value="">Todos</option>
                                @foreach ($roleLabels as $role => $label)
                                    <option value="{{ $label }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label for="statusFilter" class="form-label text-muted small text-uppercase fw-bold">Estado</label>
                            <select id="statusFilter" class="form-select table-filter" data-column="8">
                                <option value="">Todos</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
                                <option value="Suspendido">Suspendido</option>
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label for="unitFilter" class="form-label text-muted small text-uppercase fw-bold">Unidad</label>
                            <select id="unitFilter" class="form-select table-filter" data-column="6">
                                <option value="">Todas</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->name }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-2 col-md-6">
                            <label for="groupFilter" class="form-label text-muted small text-uppercase fw-bold">Grupo</label>
                            <select id="groupFilter" class="form-select table-filter" data-column="5">
                                <option value="">Todos</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->name }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-1 col-md-6">
                            <button id="clearFilters" type="button" class="btn btn-outline-secondary w-100" title="Limpiar filtros">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card customShadow">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="usersTable" class="table table-hover align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Elemento</th>
                                    <th>Correo</th>
                                    <th>Placa / ID</th>
                                    <th>Rango</th>
                                    <th>Perfil</th>
                                    <th>Grupo</th>
                                    <th>Unidad</th>
                                    <th>Credencial CogniFit</th>
                                    <th>Estado</th>
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($list as $user)
                                    @php
                                        $statusInfo = $statusLabels[(int) ($user['status'] ?? 0)] ?? $statusLabels[0];
                                        $roleLabel = $roleLabels[$user['role'] ?? 'user'] ?? 'Usuario operativo';
                                        $image = $user['image'] ?? null;
                                        $imagePath = $image ? public_path('UserImages/' . $image) : null;
                                        $avatar = $imagePath && file_exists($imagePath)
                                            ? asset('UserImages/' . $image)
                                            : asset('common/favicon.png');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $avatar }}" class="avatar avatar-sm rounded-circle" alt="Elemento">
                                                <div>
                                                    <strong>{{ $user['name'] }}</strong>
                                                    <div class="text-muted small">{{ $genderLabels[$user['gender'] ?? ''] ?? 'Sin género' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $user['email'] }}</td>
                                        <td>{{ $user['badge_number'] ?: '-' }}</td>
                                        <td>{{ $user['rank'] ?: '-' }}</td>
                                        <td>{{ $roleLabel }}</td>
                                        <td>{{ $user['operational_group'] ?: '-' }}</td>
                                        <td>{{ $user['unit'] ?: '-' }}</td>
                                        <td>
                                            @if (filled($user['cognifit_user_token'] ?? null))
                                                <span class="badge bg-success">Sincronizada</span>
                                            @elseif (($user['role'] ?? 'user') === 'user')
                                                <span class="badge bg-warning text-dark">Pendiente</span>
                                            @else
                                                <span class="badge bg-secondary">No aplica</span>
                                            @endif
                                        </td>
                                        <td><span class="badge {{ $statusInfo['class'] }}">{{ $statusInfo['label'] }}</span></td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('admin.user.profile', ['id' => $user['id']]) }}"
                                                    class="btn btn-outline-primary">
                                                    <i class="fa fa-user me-1"></i>
                                                    Perfil
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal"
                                                    data-bs-target="#deleteUserModal{{ $user['id'] }}" title="Eliminar">
                                                    <i class="fa fa-trash me-1"></i>
                                                    Eliminar
                                                </button>
                                            </div>
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

    @include('admin.users.partials.user-form-modal', [
        'modalId' => 'createUserModal',
        'title' => 'Crear elemento',
        'action' => route('admin.users.store'),
        'method' => 'POST',
        'user' => null,
        'ranks' => $ranks,
        'units' => $units,
        'groups' => $groups,
        'areas' => $areas,
        'roleLabels' => $roleLabels,
        'genderLabels' => $genderLabels,
    ])

    @foreach ($list as $user)
        <div class="modal fade" id="deleteUserModal{{ $user['id'] }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Eliminar elemento</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        Confirma que deseas eliminar a <strong>{{ $user['name'] }}</strong>. Esta acción no se puede deshacer.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <form action="{{ route('admin.users.destroy', ['id' => $user['id']]) }}" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="importModalLabel">Importar elementos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="{{ route('admin.upload.excel') }}" method="post" target="_blank" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="file">Archivo Excel</label>
                            <input id="file" type="file" class="form-control" name="file" accept=".xlsx,.xls,.csv" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">Cargar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const table = $('#usersTable').DataTable({
                dom: 'rtip',
                pageLength: 15,
                order: [[0, 'asc']],
                language: {
                    emptyTable: 'No hay elementos registrados.',
                    info: 'Mostrando _START_ a _END_ de _TOTAL_ elementos',
                    infoEmpty: 'Sin elementos para mostrar',
                    infoFiltered: '(filtrado de _MAX_ elementos)',
                    lengthMenu: 'Mostrar _MENU_ elementos',
                    loadingRecords: 'Cargando...',
                    processing: 'Procesando...',
                    search: 'Buscar:',
                    zeroRecords: 'No se encontraron coincidencias',
                    paginate: {
                        first: 'Primero',
                        last: 'Último',
                        next: 'Siguiente',
                        previous: 'Anterior'
                    }
                }
            });

            $('#userSearch').on('keyup search', function() {
                table.search(this.value).draw();
            });

            $('.table-filter').on('change', function() {
                const column = Number($(this).data('column'));
                table.column(column).search(this.value).draw();
            });

            $('#clearFilters').on('click', function() {
                $('#userSearch').val('');
                $('.table-filter').val('');
                table.search('');
                table.columns().search('');
                table.draw();
            });

            $('#exportUsersTable').on('click', function() {
                const headers = [];
                $('#usersTable thead th').each(function(index) {
                    if (index < 9) {
                        headers.push($(this).text().trim());
                    }
                });

                const rows = [headers];

                table.rows({ search: 'applied' }).every(function() {
                    const row = [];
                    $(this.node()).find('td').each(function(index) {
                        if (index < 9) {
                            row.push($(this).text().replace(/\s+/g, ' ').trim());
                        }
                    });
                    rows.push(row);
                });

                const csv = rows
                    .map(row => row.map(value => `"${String(value).replace(/"/g, '""')}"`).join(','))
                    .join('\n');
                const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                const date = new Date().toISOString().slice(0, 10);

                link.href = url;
                link.download = `elementos-${date}.csv`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            });
        });
    </script>
@endpush
