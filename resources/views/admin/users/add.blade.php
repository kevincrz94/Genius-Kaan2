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
                                <h4 class="card-title">
                                    Add User
                                </h4>
                                <a href="{{ route('admin.user.management') }}" class="btn btn-primary">
                                    <i class="fa fa-arrow-left"></i>
                                    Back
                                </a>
                            </div>
                        </div>
                        <form action="{{ route('admin.users.store') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-4 form-group">
                                        <label for="">Name</label>
                                        <input type="text" class="form-control" placeholder="Name" name="name"
                                            value="{{ old('name') }}" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Email</label>
                                        <input type="email" class="form-control" placeholder="Email" name="email"
                                            value="{{ old('email') }}" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Age</label>
                                        <input type="number" class="form-control" placeholder="Age" name="age"
                                            value="{{ old('age') }}" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Password</label>
                                        <input type="password" class="form-control" placeholder="Password" name="password"
                                            required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Confirm Password</label>
                                        <input type="password" class="form-control" placeholder="Confirm Password"
                                            name="confirm_password" required>
                                    </div>
                                    <div class="col-lg-4 form-group">
                                        <label for="">Gender</label>
                                        <select name="gender" class="form-control" required>
                                            <option selected disabled>Select Gender</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-12 form-group">
                                        <label for="">Image</label>
                                        <input type="file" class="form-control" name="image" accept="image/*" required>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        Submit
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
