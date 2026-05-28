@php
    $status = $info['status'] ?? 1;
    $imagePath = ! empty($info['image']) ? public_path('UserImages/' . $info['image']) : null;
    $displayImage = $imagePath && file_exists($imagePath)
        ? asset('UserImages/' . $info['image'])
        : asset('common/favicon.png');
@endphp

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte del elemento - {{ $info['name'] ?? 'Elemento' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('common/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
</head>

<body class="report-page">
    <div class="container mb-5">
        <div class="d-flex justify-content-end gap-2 mt-4 no-print">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fa fa-print"></i> Imprimir
            </button>
            <button id="downloadPdf" class="btn btn-primary">
                <i class="fa fa-file-pdf"></i> Descargar PDF
            </button>
        </div>

        <div class="report-card report-document" id="report">
            <div class="d-flex justify-content-between align-items-center mb-4 report-official-header">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset('common/light-logo.png') }}" class="report-logo" alt="Genius Kaan">
                    <div>
                        <h4 class="mb-0 text-uppercase fw-bold report-title">Genius Kaan</h4>
                        <span class="text-muted small">Reporte de Aptitud Cognitiva Operativa</span>
                    </div>
                </div>
                <div class="text-end text-muted small">
                    <strong>Fecha de emisión:</strong> {{ $issuedAt->format('d/m/Y H:i') }}<br>
                    <strong>Folio:</strong> {{ $folio }}
                </div>
            </div>

            <h3 class="mb-3">Desempeño del elemento: {{ $info['name'] ?? 'No especificado' }}</h3>

            <div class="row mt-4">
                <div class="col-8">
                    <h5>Datos del elemento</h5>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150"><strong>Nombre:</strong></td>
                            <td>{{ $info['name'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Correo:</strong></td>
                            <td>{{ $info['email'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Género:</strong></td>
                            <td>{{ $info['gender'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Placa / ID:</strong></td>
                            <td>{{ $info['badge_number'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Unidad:</strong></td>
                            <td>{{ $info['unit'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Estado:</strong></td>
                            <td>
                                <span
                                    class="badge bg-{{ $status == 2 ? 'danger' : ($status == 1 ? 'success' : 'warning') }}">
                                    {{ $status == 2 ? 'Suspendido' : ($status == 1 ? 'Activo' : 'Inactivo') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>

                <div class="col-4 text-end">
                    <img src="{{ $displayImage }}" width="80" alt="Elemento">
                </div>
            </div>

            <hr>

            <div class="row mt-4">
                <div class="col-md-6">
                    <h6 class="report-section-title">Objetivos seleccionados</h6>
                    <ul class="list-group list-group-flush border">
                        @forelse($goals as $goal)
                            <li class="list-group-item small">
                                {{ is_string($goal) ? $goal : $goal['name'] ?? 'Sin nombre' }}
                            </li>
                        @empty
                            <li class="list-group-item small text-muted">Sin objetivos seleccionados</li>
                        @endforelse
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="report-section-title">Áreas de enfoque</h6>
                    <ul class="list-group list-group-flush border">
                        @forelse($areas as $area)
                            <li class="list-group-item small">
                                {{ is_string($area) ? $area : $area['name'] ?? 'Sin nombre' }}
                            </li>
                        @empty
                            <li class="list-group-item small text-muted">Sin áreas seleccionadas</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <h6 class="report-section-title">Métricas de entrenamiento operativo</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-2">
                    <thead class="table-light">
                        <tr>
                            <th>Evaluación</th>
                            <th class="text-center">Puntaje</th>
                            <th class="text-end">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(array_slice($playedGames, 0, 10) as $activity)
                            <tr>
                                <td>{{ $brainGameTitles[$activity['key'] ?? ''] ?? ($activity['key'] ?? 'Sin nombre') }}</td>
                                <td class="text-center fw-bold text-primary">{{ $activity['score'] ?? '-' }}</td>
                                <td class="text-end text-muted">
                                    {{ isset($activity['time']) ? \Carbon\Carbon::parse($activity['time'])->format('d M Y') : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            @forelse(($localSessions ?? collect())->take(10) as $session)
                                <tr>
                                    <td>{{ $session->game_key ?? 'Sesión CogniFit' }}</td>
                                    <td class="text-center fw-bold text-primary">{{ $session->score ?? '-' }}</td>
                                    <td class="text-end text-muted">
                                        {{ optional($session->completed_at)->format('d M Y') ?: optional($session->created_at)->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">Sin métricas de entrenamiento.</td>
                                </tr>
                            @endforelse
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <script>
        document.getElementById('downloadPdf').addEventListener('click', function() {
            const element = document.getElementById('report');
            const opt = {
                margin: 0.4,
                filename: 'reporte-aptitud-cognitiva-{{ $folio }}.pdf',
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2,
                    useCORS: true
                },
                jsPDF: {
                    unit: 'in',
                    format: 'letter',
                    orientation: 'portrait'
                }
            };

            html2pdf().set(opt).from(element).output('blob').then(function(blob) {
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = opt.filename;
                a.click();
            });
        });
    </script>
</body>

</html>
