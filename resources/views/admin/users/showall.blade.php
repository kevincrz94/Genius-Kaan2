<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Admin - Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        {{-- <h2>Users CRUD</h2> --}}

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card mb-4">
            <div class="card-header">{{ isset($user) ? 'Edit User' : 'Add New User' }}</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data"
                    action="{{ isset($user) ? route('admin.users.update', $user->id) : route('admin.users.store') }}">
                    @csrf
                    @if (isset($user))
                        @method('POST')
                    @endif
                    <div class="mb-3">
                        <input type="text" name="name" class="form-control" placeholder="Name"
                            value="{{ $user->name ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email"
                            value="{{ $user->email ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <input type="file" name="image" class="form-control" {{ isset($user) ? '' : 'required' }}>
                        @if (isset($user) && $user->image)
                            <img src="{{ asset('UserImages/' . $user->image) }}" width="80" class="mt-2">
                        @endif
                    </div>
                    <div class="mb-3">
                        <input type="number" name="age" class="form-control" placeholder="Age"
                            value="{{ $user->age ?? '' }}" required>
                    </div>
                    <div class="mb-3">
                        <select name="gender" class="form-control" required>
                            <option value="">Select Gender</option>
                            <option value="male" {{ isset($user) && $user->gender == 'male' ? 'selected' : '' }}>Male
                            </option>
                            <option value="female" {{ isset($user) && $user->gender == 'female' ? 'selected' : '' }}>
                                Female
                            </option>
                            <option value="other" {{ isset($user) && $user->gender == 'other' ? 'selected' : '' }}>
                                Other
                            </option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Password"
                            {{ isset($user) ? '' : 'required' }}>
                    </div>
                    <button type="submit" class="btn btn-primary">{{ isset($user) ? 'Update' : 'Add' }}</button>
                    @if (isset($user))
                        <a href="{{ route('admin.users') }}" class="btn btn-secondary">Cancel</a>
                    @endif
                </form>
            </div>
        </div>

        {{-- <h3>All Users</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Image</th>
                <th>Age</th>
                <th>Gender</th>
                <th>Actions</th>
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
                    <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-warning">Edit</a>
                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table> --}}
    </div>
</body>

</html>
