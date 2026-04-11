@php
    $viewData = \App\Services\customBlock::class;
    $image = $viewData::printData($info, 'image');
    $status = $viewData::printData($info, 'status') ?? 1;

    // Goals aur Areas ko parse karein
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
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>User Report - {{ $info['name'] ?? 'User' }}</title>
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
                <i class="fa fa-print"></i> Print
            </button>
            <button id="downloadPdf" class="btn btn-primary">
                <i class="fa fa-file-pdf"></i> Download PDF
                </but>
        </div>

        <div class="report-card" id="report">
            <div class="header-border d-flex justify-content-between align-items-center">
                <h2 class="text-primary m-0">User Progress Report</h2>
                <span class="text-muted">Date: {{ date('d M Y') }}</span>
            </div>

            <div class="row mt-4">
                <div class="col-8">
                    <h5>Personal Details</h5>
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td width="150"><strong>Name:</strong></td>
                            <td>{{ $info['name'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $info['email'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Gender:</strong></td>
                            <td>{{ $info['gender'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span
                                    class="badge bg-{{ $status == 2 ? 'danger' : ($status == 1 ? 'success' : 'warning') }}">
                                    {{ $status == 2 ? 'Suspended' : ($status == 1 ? 'Active' : 'Inactive') }}
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
                    <h6 class="section-title">Selected Goals</h6>
                    <ul class="list-group list-group-flush border">
                        @forelse($goals as $goal)
                            <li class="list-group-item small">
                                {{ is_string($goal) ? $goal : $goal['name'] ?? 'Unknown' }}</li>
                        @empty
                            <li class="list-group-item small text-muted">No goals selected</li>
                        @endforelse
                    </ul>
                </div>
                <div class="col-md-6">
                    <h6 class="section-title">Focus Areas</h6>
                    <ul class="list-group list-group-flush border">
                        @forelse($areas as $area)
                            <li class="list-group-item small">
                                {{ is_string($area) ? $area : $area['name'] ?? 'Unknown' }}</li>
                        @empty
                            <li class="list-group-item small text-muted">No areas selected</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <h6 class="section-title">Game Activity (Recent 10)</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped mt-2">
                    <thead class="table-light">
                        <tr>
                            <th>Game Name</th>
                            <th class="text-center">Score</th>
                            <th class="text-end">Date Played</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(array_slice($playedGames, 0, 10) as $game)
                            <tr>
                                <td>
                                    @php
                                        // Game name fetch karne ka wahi tareeka jo profile mein tha
                                        $gameName = $game['key'] ?? 'Unknown';
                                        if (isset($brainGames) && !is_array($brainGames)) {
                                            $gameObj = $brainGames->where('key', $game['key'] ?? '')->first();
                                            $gameName = $gameObj->assets->titles->en ?? $gameName;
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
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">No game records found.</td>
                            </tr>
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
