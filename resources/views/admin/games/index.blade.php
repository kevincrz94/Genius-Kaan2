@extends('admin.layouts.main')

@section('section')
    @php
        $customFunction = \App\Services\customBlock::class;
    @endphp

    <div class="page-wrapper">
        <div class="content container-fluid">

            {{-- Search Bar Section --}}
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card customShadow">
                        <div class="card-body">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <i class="fas fa-search me-2 text-muted"></i>
                                    <input type="text" id="gameSearch" class="form-control"
                                        placeholder="Search games by title or skill...">
                                </div>
                                <div class="ms-3">
                                    <span class="badge bg-primary" id="gameCount"
                                        style="font-size: 0.9rem; padding: 0.5rem 1rem;">
                                        Showing <span id="visibleCount">{{ count($list) }}</span> of <span
                                            id="totalCount">{{ count($list) }}</span> games
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- No Results Message (hidden by default) --}}
            <div class="row mb-3" id="noResults" style="display: none;">
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <i class="fas fa-info-circle me-2"></i>No games found matching your search.
                    </div>
                </div>
            </div>

            <div class="row" id="gamesContainer">
                @foreach ($list as $item)
                    <div class="col-lg-3 game-card" data-title="{{ strtolower($item->assets->titles->en) }}"
                        data-skills="{{ strtolower(implode(' ', $item->skills)) }}">
                        <div class="card customShadow">
                            <div class="card-header">
                                <div class="d-flex justify-content-start gap-2 align-items-center">
                                    <div class="d-flex justify-content-start gap-2 align-items-center">
                                        <img class="avatar-img rounded-circle" style="width: 150px;" alt="User Image"
                                            src="{{ $item->assets->images->icon }}">
                                        <div>
                                            <h4 class="card-title">{{ $item->assets->titles->en }}</h4>
                                            @foreach ($item->skills as $skillName)
                                                <span class="badge badge-primary">
                                                    {{ $customFunction::processStringNames($skillName) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input enableGame" type="checkbox" role="switch"
                                            id="switchCheckDefault" data-bs-id="{{ $item->key }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                @php
                                    $description = $item->assets->descriptions->en;
                                    $truncated = \Illuminate\Support\Str::words($description, 20, '...');
                                @endphp
                                <p>
                                    {{ $truncated }}
                                    @if (\Illuminate\Support\Str::wordCount($description) > 20)
                                        <a href="javascript:void(0);" class="text-primary view-more-btn"
                                            data-title="{{ $item->assets->titles->en }}"
                                            data-description="{{ $description }}">
                                            View More
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

    <!-- View More Modal -->
    <div class="modal fade" id="viewMoreModal" tabindex="-1" aria-labelledby="viewMoreModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewMoreModalLabel">Game Description</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="modalDescription"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // View More Modal functionality
            $('.view-more-btn').on('click', function() {
                var title = $(this).data('title');
                var description = $(this).data('description');

                $('#viewMoreModalLabel').text(title);
                $('#modalDescription').text(description);
                $('#viewMoreModal').modal('show');
            });

            // Search functionality
            $('#gameSearch').on('keyup', function() {
                var searchQuery = $(this).val().toLowerCase().trim();
                var visibleCount = 0;
                var totalCount = $('.game-card').length;

                $('.game-card').each(function() {
                    var title = $(this).data('title');
                    var skills = $(this).data('skills');

                    // Check if search query matches title or skills
                    if (title.includes(searchQuery) || skills.includes(searchQuery)) {
                        $(this).show();
                        visibleCount++;
                    } else {
                        $(this).hide();
                    }
                });

                // Update count badge
                $('#visibleCount').text(visibleCount);

                // Change badge color based on filter status
                var badge = $('#gameCount');
                if (searchQuery === '') {
                    badge.removeClass('bg-info bg-warning').addClass('bg-primary');
                } else if (visibleCount === 0) {
                    badge.removeClass('bg-primary bg-info').addClass('bg-warning');
                } else {
                    badge.removeClass('bg-primary bg-warning').addClass('bg-info');
                }

                // Show/hide "no results" message
                if (visibleCount === 0) {
                    $('#noResults').show();
                } else {
                    $('#noResults').hide();
                }
            });
        });
    </script>

    <script type="module">
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/11.9.1/firebase-app.js";
        import {
            getAnalytics
        } from "https://www.gstatic.com/firebasejs/11.9.1/firebase-analytics.js";
        import {
            getDatabase,
            ref,
            onValue,
            set,
            push,
            remove
        } from "https://www.gstatic.com/firebasejs/11.9.1/firebase-database.js";

        // Firebase config
        const firebaseConfig = {
            apiKey: "AIzaSyDWyXth9kde5FAaA_hGAPsFq9CHCIzR8wc",
            authDomain: "impulse-9f2b2.firebaseapp.com",
            databaseURL: "https://impulse-9f2b2-default-rtdb.firebaseio.com",
            projectId: "impulse-9f2b2",
            storageBucket: "impulse-9f2b2.firebasestorage.app",
            messagingSenderId: "881922859246",
            appId: "1:881922859246:web:cfe6d67e494055d84453ff",
            measurementId: "G-GYV0KE70ED"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const analytics = getAnalytics(app);
        const db = getDatabase(app);

        document.addEventListener("DOMContentLoaded", function() {
            const listGames = document.querySelectorAll(".enableGame");

            // Reference to disabledGames in Firebase
            const disabledGamesRef = ref(db, "disabledGames");

            // Load initial state from Firebase
            onValue(disabledGamesRef, (snapshot) => {
                // First, reset all checkboxes
                listGames.forEach(el => el.checked = true);

                snapshot.forEach((childSnapshot) => {
                    const childKey = childSnapshot.key;
                    listGames.forEach(el => {
                        if (el.dataset.bsId === childKey) {
                            el.checked = false; // disable checkbox
                        }
                    });
                });
            });

            // Add toggle event listener for each checkbox
            listGames.forEach(el => {
                el.addEventListener("change", () => {
                    const gameKey = el.dataset.bsId;
                    const gameRef = ref(db, `disabledGames/${gameKey}`);

                    if (!el.checked) {
                        // If unchecked, add to Firebase
                        set(gameRef, gameKey);
                    } else {
                        // If checked, remove from Firebase
                        remove(gameRef);
                    }
                });
            });
        });
    </script>
@endsection
