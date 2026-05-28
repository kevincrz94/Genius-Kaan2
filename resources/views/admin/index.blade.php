@extends('admin.layouts.main')

@section('section')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Mando y Supervisión General</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Panel Consolidado de Aptitud Cognitiva</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card customShadow border-start border-primary border-3">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon text-primary border-primary bg-light">
                                    <i class="fe fe-users"></i>
                                </span>
                                <div class="dash-count">
                                    <h3>{{ $totalElements ?? count($list) }}</h3>
                                </div>
                            </div>
                            <div class="dash-widget-info mt-2">
                                <h6 class="text-muted text-uppercase small">Elementos registrados</h6>
                                <div class="progress progress-sm mt-2" role="progressbar" aria-label="Elementos registrados"
                                    aria-valuenow="100" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-primary w-100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card customShadow border-start border-danger border-3">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon text-danger border-danger bg-light">
                                    <i class="fe fe-alert-triangle"></i>
                                </span>
                                <div class="dash-count">
                                    <h3>{{ $alertCount ?? 0 }}</h3>
                                </div>
                            </div>
                            <div class="dash-widget-info mt-2">
                                <h6 class="text-muted text-uppercase small">Alertas de refuerzo</h6>
                                <div class="progress progress-sm mt-2" role="progressbar" aria-label="Alertas de refuerzo"
                                    aria-valuenow="{{ $alertProgress ?? 0 }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-danger" style="width: {{ $alertProgress ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card customShadow border-start border-success border-3">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon text-success border-success bg-light">
                                    <i class="fe fe-activity"></i>
                                </span>
                                <div class="dash-count">
                                    <h3>{{ $globalIndex ?? 0 }}<span class="fs-6 text-muted">/100</span></h3>
                                </div>
                            </div>
                            <div class="dash-widget-info mt-2">
                                <h6 class="text-muted text-uppercase small">Índice de aptitud global</h6>
                                <div class="progress progress-sm mt-2" role="progressbar"
                                    aria-label="Índice de aptitud global" aria-valuenow="{{ $globalIndex ?? 0 }}"
                                    aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-success" style="width: {{ $globalIndex ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-sm-6 col-12">
                    <div class="card customShadow border-start border-info border-3">
                        <div class="card-body">
                            <div class="dash-widget-header">
                                <span class="dash-widget-icon text-info border-info bg-light">
                                    <i class="fe fe-link"></i>
                                </span>
                                <div class="dash-count">
                                    <h3>{{ $syncPercentage ?? 0 }}<span class="fs-6 text-muted">%</span></h3>
                                </div>
                            </div>
                            <div class="dash-widget-info mt-2">
                                <h6 class="text-muted text-uppercase small">Sincronización de credenciales</h6>
                                <div class="progress progress-sm mt-2" role="progressbar"
                                    aria-label="Sincronización de credenciales" aria-valuenow="{{ $syncPercentage ?? 0 }}"
                                    aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar bg-info" style="width: {{ $syncPercentage ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <div class="col-lg-8 col-12">
                    <div class="card customShadow">
                        <div class="card-header bg-white border-bottom-0 pt-4">
                            <h5 class="card-title text-secondary mb-0">Estructura y Trazabilidad por Unidad Operativa</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-4">
                                Selecciona una división, zona o grupo operativo para auditar reportes de fatiga,
                                control inhibitorio, atención sostenida y toma de decisiones.
                            </p>

                            <div class="row">
                                <div class="col-md-4 col-12 mb-3">
                                    <div class="p-3 border rounded bg-light h-100">
                                        <span class="text-muted small text-uppercase">Unidades</span>
                                        <h4 class="mb-0 mt-2">{{ $unitCount ?? 0 }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12 mb-3">
                                    <div class="p-3 border rounded bg-light h-100">
                                        <span class="text-muted small text-uppercase">Grupos operativos</span>
                                        <h4 class="mb-0 mt-2">{{ $groupCount ?? 0 }}</h4>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12 mb-3">
                                    <div class="p-3 border rounded bg-light h-100">
                                        <span class="text-muted small text-uppercase">Sesiones 30 días</span>
                                        <h4 class="mb-0 mt-2">{{ $recentSessions ?? 0 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-12">
                    <div class="card customShadow">
                        <div class="card-header bg-white border-bottom-0 pt-4">
                            <h5 class="card-title text-secondary mb-0">Credenciales CogniFit</h5>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                {{ $syncedTokens ?? 0 }} de {{ $totalElements ?? 0 }} elementos cuentan con credencial
                                lista para iniciar módulos de entrenamiento.
                            </p>
                            <a href="{{ route('admin.user.management') }}" class="btn btn-primary w-100">
                                Gestionar elementos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
