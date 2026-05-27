@extends('admin.layouts.main')

@section('section')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <div class="row">
                <div class="col-sm-12">
                    <div class="card customShadow">
                        <div class="card-header">
                            <h4 class="card-title">Habilidades: {{ $list->count() }}</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="datatable table table-stripped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Icono</th>
                                            <th>Nombre</th>
                                            <th>Clave</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($list as $item)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="javascript:void(0)" class="avatar avatar-sm me-2">
                                                            <img class="avatar-img rounded-circle"
                                                                src="{{ $item->assets->images->whiteIcon }}"
                                                                alt="Icono" style="background: #00254C;">
                                                        </a>
                                                    </h2>
                                                </td>
                                                <td>{{ $item->assets->titles->es ?? $item->assets->titles->en }}</td>
                                                <td>{{ $item->key }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
