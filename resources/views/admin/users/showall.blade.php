<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Admin - Elementos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        {{-- <h2>Gestion de elementos</h2> --}}

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-header">{{ isset($user) ? 'Editar elemento' : 'Agregar elemento' }}</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data"
                    action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}">
                    @csrf
                    @if (isset($user))
                        @method('POST')
                    @endif
                    <div class="mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Nombre"
                            value="{{ $user->name ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Correo"
                            value="{{ $user->email ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="image" class="form-control" {{ isset($user) ? '' : 'required' }}>
                        @if (isset($user) && $user->image)
                            <img src="{{ asset('UserImages/' . $user->image) }}" width="80" class="mt-2">
                        @endif
                    </div>
                    <div class="mb-3">
                        <input type="number" name="age" class="form-control" placeholder="Edad"
                            value="{{ $user->age ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <select name="gender" class="form-control" required>
                            <option value="">Selecciona genero</option>
                            <option value="male" {{ isset($user) && $user->gender == 'male' ? 'selected' : '' }}>Masculino
                            </option>
                            <option value="female" {{ isset($user) && $user->gender == 'female' ? 'selected' : '' }}>
                                Femenino
                            </option>
                            <option value="other" {{ isset($user) && $user->gender == 'other' ? 'selected' : '' }}>
                                Otro
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Contrasena"
                            {{ isset($user) ? '' : 'required' }}>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Actualizar' : 'Agregar' }}</button>
                    @if (isset($user))
                        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Cancelar</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- <h3>Todos los elementos</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Imagen</th>
                <th>Edad</th>
                <th>Genero</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($users as $u)
            <tr>
                <td>{{ $u->id }}</td>
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>
                    @if ($u->image)
                        <img src="{{ asset('UserImages/'.$u->image) }}" width="50">
                    @endif
                </td>
                <td>{{ $u->age }}</td>
                <td>{{ $u->gender }}</td>
                <td>
                    <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confirma que deseas eliminar este elemento')">Eliminar</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table> --}}
    </div>
</body>

</html>
