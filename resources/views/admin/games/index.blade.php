@extends('admin.layouts.main')

@section('section')
    @php
        $customFunction = \App\Services\customBlock::class;
        $totalModules = count($list);
        $firebaseConfig = array_filter(config('services.firebase_web'), fn ($value) => filled($value));
    @endphp

    <div class="page-wrapper">
        <div class="content container-fluid">

            @if (empty($firebaseConfig))
                <div class="alert alert-warning">
                    Configura las variables FIREBASE_WEB_* en el archivo .env para habilitar la sincronización de simuladores.
                </div>
            @endif

            <div class="row mb-4">
                <div class="col-12">
                    <div class="card customShadow">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <i class="fas fa-search me-3 text-muted fs-5"></i>
                                    <input type="text" id="moduleSearch" class="form-control border-0 bg-light py-2"
                                        placeholder="Buscar simuladores por título o capacidad cognitiva...">
                                </div>
                                <div>
                                    <span class="badge bg-primary fs-6 py-2 px-3" id="moduleCount">
                                        Mostrando <span id="visibleCount">{{ $totalModules }}</span> de
                                        <span id="totalCount">{{ $totalModules }}</span> simuladores
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mb-3 d-none" id="noResults">
                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No se encontraron simuladores con ese criterio.
                    </div>
                </div>
            </div>

            <div class="row" id="modulesContainer">
                @foreach ($list as $item)
                    @php
                        $title = $item->assets->titles->es ?? $item->assets->titles->en ?? $item->key;
                        $description = $item->assets->descriptions->es ?? $item->assets->descriptions->en ?? '';
                        $truncated = \Illuminate\Support\Str::words($description, 18, '...');
                        $skills = is_array($item->skills ?? null) ? $item->skills : [];
                    @endphp
                    <div class="col-lg-3 col-md-6 mb-4 module-card"
                        data-title="{{ strtolower($title) }}"
                        data-skills="{{ strtolower(implode(' ', $skills)) }}">
                        <div class="card customShadow h-100">
                            <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                                <div class="d-flex justify-content-between align-items-start gap-2">
                                    <img class="module-avatar rounded-circle shadow-sm" alt="Módulo"
                                        src="{{ $item->assets->images->icon }}">

                                    <div class="form-check form-switch mt-1">
                                        <input class="form-check-input enableModule fs-5" type="checkbox" role="switch"
                                            data-bs-id="{{ $item->key }}" @disabled(empty($firebaseConfig))>
                                    </div>
                                </div>
                                <h5 class="card-title mt-3 mb-1 lh-sm module-title">
                                    {{ $title }}
                                </h5>
                                <div class="d-flex flex-wrap gap-1 mt-2">
                                    @foreach (array_slice($skills, 0, 2) as $skillName)
                                        <span class="badge bg-light text-secondary border">
                                            {{ $customFunction::processStringNames($skillName) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="card-body">
                                <p class="text-muted small mb-0">
                                    {{ $truncated }}
                                    @if (\Illuminate\Support\Str::wordCount($description) > 18)
                                        <a href="javascript:void(0);"
                                            class="text-primary fw-bold view-more-btn text-decoration-none ms-1"
                                            data-title="{{ $title }}"
                                            data-description="{{ $description }}">
                                            Leer más
                                        </a>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    <div class="modal fade" id="viewMoreModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary" id="viewMoreModalLabel">Detalle del simulador</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body p-4">
                    <p id="modalDescription" class="text-muted mb-0 lh-lg"></p>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('.view-more-btn').on('click', function() {
                $('#viewMoreModalLabel').text($(this).data('title'));
                $('#modalDescription').text($(this).data('description'));
                $('#viewMoreModal').modal('show');
            });

            $('#moduleSearch').on('keyup', function() {
                const searchQuery = $(this).val().toLowerCase().trim();
                let visibleCount = 0;

                $('.module-card').each(function() {
                    const title = $(this).data('title');
                    const skills = $(this).data('skills');

                    if (title.includes(searchQuery) || skills.includes(searchQuery)) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                $('#visibleCount').text(visibleCount);

                const badge = $('#moduleCount');
                badge.removeClass('bg-primary bg-info bg-warning text-dark');

                if (searchQuery === '') {
                    badge.addClass('bg-primary');
                    $('#noResults').addClass('d-none');
                } else if (visibleCount === 0) {
                    badge.addClass('bg-warning text-dark');
                    $('#noResults').removeClass('d-none');
                } else {
                    badge.addClass('bg-info text-dark');
                    $('#noResults').addClass('d-none');
                }
            });
        });
    </script>

    @if (! empty($firebaseConfig))
        <script type="module">
            import { initializeApp } from "https://www.gstatic.com/firebasejs/11.9.1/firebase-app.js";
            import { getDatabase, ref, onValue, set, remove } from "https://www.gstatic.com/firebasejs/11.9.1/firebase-database.js";

            const firebaseConfig = @json($firebaseConfig);
            const app = initializeApp(firebaseConfig);
            const db = getDatabase(app);

            document.addEventListener('DOMContentLoaded', function() {
                const listModules = document.querySelectorAll('.enableModule');
                const disabledModulesRef = ref(db, 'disabledGames');

                onValue(disabledModulesRef, (snapshot) => {
                    listModules.forEach((el) => el.checked = true);

                    snapshot.forEach((childSnapshot) => {
                        const childKey = childSnapshot.key;
                        listModules.forEach((el) => {
                            if (el.dataset.bsId === childKey) {
                                el.checked = false;
                            }
                        });
                    });
                });

                listModules.forEach((el) => {
                    el.addEventListener('change', () => {
                        const moduleKey = el.dataset.bsId;
                        const moduleRef = ref(db, `disabledGames/${moduleKey}`);

                        if (!el.checked) {
                            set(moduleRef, moduleKey)
                                .then(() => toastr.warning('Simulador deshabilitado en la plataforma.'))
                                .catch(() => toastr.error('Error de conexión con Firebase.'));
                            return;
                        }

                        remove(moduleRef)
                            .then(() => toastr.success('Simulador habilitado correctamente.'))
                            .catch(() => toastr.error('Error de conexión con Firebase.'));
                    });
                });
            });
        </script>
    @endif
@endpush
