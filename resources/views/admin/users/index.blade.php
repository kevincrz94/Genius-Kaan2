@extends('admin.layouts.main')

@section('section')
    @php
        $viewData = \App\Services\customBlock::class;
    @endphp

    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">

                <div class="col-lg-3">
                    <div class="card customShadow">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="card-title">
                                    Elementos ({{ count($list) }})
                                </h4>
                                <a href="{{ route('admin.user.management.add') }}" class="btn btn-primary">
                                    <i class="fa fa-plus"></i>
                                    Agregar
                                </a>
                            </div>
                        </div>
                        <div class="card-body px-2 py-2">
                            <div class="d-flex justify-content-between align-items center">
                                <a href="{{ route('admin.download.excel') }}" class="btn btn-primary btn-rounded">
                                    <i class="fa-solid fa-file-excel"></i>
                                    Descargar plantilla
                                </a>
                                <button class="btn btn-primary btn-rounded" type="button" data-bs-toggle="modal"
                                    data-bs-target="#importModal">
                                    <i class="fa fa-download"></i>
                                    Importar elementos
                                </button>
                            </div>
                        </div>
                        <ul class="list-group" style="max-height:80vh; overflow-y:auto">
                            @foreach ($list as $user)
                                <li class="list-group-item d-flex align-items-center user-item"
                                    data-id="{{ $viewData::printData($user, 'id') }}" style="cursor:pointer">
                                    @php
                                        $image = $viewData::printData($user, 'image');
                                        $path = $image != '-' ? public_path('UserImages/' . $image) : null;
                                    @endphp
                                    <img src="{{ $path && file_exists($path) ? asset('UserImages/' . $image) : asset('common/favicon.png') }}"
                                        class="avatar avatar-sm rounded-circle me-2">
                                    <span>{{ $viewData::printData($user, 'name') }}</span>
                                    <br>
                                    <span>{{ $viewData::printData($user, 'email') }}</span>
                                    <br>
                                    <small>{{ $viewData::printData($user, 'unit') }} / {{ $viewData::printData($user, 'badge_number') }}</small>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-lg-9">
                    <div id="user-details">
                        <div class="alert alert-info text-center">
                            Selecciona un elemento para ver el detalle.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="exampleModalLabel">Importar elementos</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <form action="{{ route('admin.upload.excel') }}" method="post" target="_blank"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="">Archivo Excel</label>
                            <input type="file" class="form-control" name="file" accept="excel/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary">
                            Cargar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $(".user-item").on('click', function() {
                let userId = $(this).data("id");

                // Active class
                $(".user-item").removeClass("active bg-primary text-white");
                $(this).addClass("active bg-primary text-white");

                // Loading spinner
                $("#user-details").html(`
                    <div class="text-center py-5">
                    <div class="spinner-border text-primary" style="width:3rem;height:3rem;"></div>
                </div>
                `);

                // YE LINE SABSE IMPORTANT HAI – AB 100% SAHI HAI
                $.get("/admin/users/" + userId, function(res) {
                    if (res.status === true) {
                        $("#user-details").html(res.html);
                    } else {
                        $("#user-details").html(
                            '<div class="alert alert-danger">Elemento no encontrado</div>');
                    }
                }).fail(function(xhr) {
                    console.log(xhr.responseText);
                    $("#user-details").html(
                        '<div class="alert alert-danger">Error al cargar el detalle.</div>'
                    );
                });
            });

            // Pehla user automatically load kar do
            // if ($(".user-item").length > 0) {
            //     $(".user-item").first().click();
            // }
        });
    </script>

    <style>
        .user-item {
            transition: 0.2s;
            cursor: pointer;
        }

        .user-item:hover {
            background: #f5f5f5;
        }

        .user-item.active {
            background: #007bff !important;
            color: white !important;
            font-weight: 600;
        }

        .user-item.active img {
            /* filter: brightness(0) invert(1); */
        }
    </style>
@endsection
