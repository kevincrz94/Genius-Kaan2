@php
    $viewData = \App\Services\customBlock::class;
    $image = $viewData::printData($info, 'image');
    $status = $viewData::printData($info, 'status') ?? 1;

    // Se normalizan objetivos y áreas para el reporte.
    $goals = [];
    $areas = [];

    if (isset($info['userIntrest']['goals']) && is_array($info['userIntrest']['goals'])) {
        $goals = array_values($info['userIntrest']['goals']);
    }
    if (isset($info['userIntrest']['areas']) && is_array($info['userIntrest']['areas'])) {
        $areas = array_values($info['userIntrest']['areas']);
    }
@endphp

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte del elemento - {{ $info['name'] ?? 'Elemento' }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('common/favicon.png') }}">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .report-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }

        .header-border {
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .section-title {
            margin-top: 30px;
            margin-bottom: 15px;
            font-weight: bold;
            color: #333;
            border-left: 4px solid #0d6efd;
            padding-left: 10px;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: white;
                padding: 0;
            }

            .report-card {
                box-shadow: none;
                margin: 0;
                padding: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="container mb-5">
        <div class="d-flex justify-content-end gap-2 mt-4 no-print">
            <button onclick="window.print()" class="btn btn-secondary">
                <i class="fa fa-print"></i> Imprimir
            </button>
            <button id="downloadPdf" class="btn btn-primary">
                <i class="fa fa-file-pdf"></i> Descargar PDF
                </but>
        </div>

        <div class="report-card" id="report">
            <div class="header-border d-flex justify-content-between align-items-center">
                <h2 class="text-primary m-0">Reporte de progreso operativo</h2>
                <span class="text-muted">Fecha: {{ date('d M Y') }}</span>
            </div>

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
                @php
                    $imagePath = !empty($info['image']) ? public_path('UserImages/' . $info['image']) : null;
                    $displayImage =
                        $imagePath && file_exists($imagePath)
                            ? asset('UserImages/' . $info['image'])
                            : asset('common/favicon.png');
                @endphp

                <div class="col-4 text-end">
                    <img src="{{ $displayImage }}" width="80" alt="Logo">
                </div>

            </div>

            <hr>

            <div class="row mt-4">
                <div class="col-md-6">
                    <h6 class="section-title">Objetivos seleccionados</h6>
                    <ul class="list-group list-group-flush border">
                        @forelse($goals as $goal)
                            <li class="list-group-item small">
                                {{ is_string($goal) ? $goal : $goal['name'] ?? 'Sin nombre' }}</li>
                        @empty
                            <li class="list-group-item small text-muted">Sin objetivos seleccionados</li>
                        @endforelse
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="section-title">Áreas de enfoque</h6>
                    <ul class="list-group list-group-flush border">
                        @forelse($areas as $area)
                            <li class="list-group-item small">
                                {{ is_string($area) ? $area : $area['name'] ?? 'Sin nombre' }}</li>
                        @empty
                            <li class="list-group-item small text-muted">Sin áreas seleccionadas</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <h6 class="section-title">Actividad de juegos (últimos 10)</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-2">
                    <thead class="table-light">
                        <tr>
                            <th>Juego</th>
                            <th class="text-center">Puntaje</th>
                            <th class="text-end">Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(array_slice($playedGames, 0, 10) as $game)
                            <tr>
                                <td>
                                    @php
                                        // Game name fetch karne ka wahi tareeka jo profile mein tha
                                        $gameName = $game['key'] ?? 'Sin nombre';
                                        if (isset($brainGames) && !is_array($brainGames)) {
                                            $gameObj = $brainGames->where('key', $game['key'] ?? '')->first();
                                            $gameName = $gameObj->assets->titles->es ?? $gameObj->assets->titles->en ?? $gameName;
                                        }
                                    @endphp
                                    {{ $gameName }}
                                </td>
                                <td class="text-center fw-bold text-primary">{{ $game['score'] ?? '-' }}</td>
                                <td class="text-end text-muted">
                                    {{ isset($game['time']) ? \Carbon\Carbon::parse($game['time'])->format('d M Y') : 'N/A' }}
                                </td>
                            </tr>
                        @empty
                            @forelse(($localSessions ?? collect())->take(10) as $session)
                                <tr>
                                    <td>{{ $session->game_key ?? 'Sesion CogniFit' }}</td>
                                    <td class="text-center fw-bold text-primary">{{ $session->score ?? '-' }}</td>
                                    <td class="text-end text-muted">
                                        {{ optional($session->completed_at)->format('d M Y') ?: optional($session->created_at)->format('d M Y') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-3 text-muted">Sin registros de juego.</td>
                                </tr>
                            @endforelse
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- html2pdf CDN - client-side PDF generation -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <script>
        document.getElementById('downloadPdf').addEventListener('click', function() {
            const element = document.getElementById('report');
            const opt = {
                margin: 0.4,
                filename: 'report.pdf',
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
                // auto-download
                const a = document.createElement('a');
                a.href = url;
                a.download = 'report.pdf';
                a.click();
            });

        });
    </script>

</body>

</html>
