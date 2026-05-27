<div class="header d-flex align-items-center justify-content-between px-4 py-2 shadow-sm bg-white">

    <!-- Logo -->
    <div class="header-left d-flex align-items-center">
        <a href="{{ route('admin.dashboard') }}" class="logo d-flex align-items-center me-3">
            <img src="{{ asset('common/small-logo.png') }}" alt="Logo" class="me-2">
            <span class="fw-bold fs-5 text-dark">Panel operativo</span>
        </a>
        <a href="{{ route('admin.dashboard') }}" class="logo logo-small d-none">
            <img src="{{ asset('common/small-logo.png') }}" alt="Logo" width="30" height="30">
        </a>
    </div>
    <!-- /Logo -->

    <!-- Navigation Buttons -->
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.user.management') }}"
            class="btn {{ Route::is(['admin.user.management']) ? 'btn-primary' : 'btn-outline-primary' }} px-4 py-2 fw-semibold">
            Elementos
        </a>
        <a href="{{ route('admin.metrics.index') }}"
            class="btn {{ Route::is(['admin.metrics.*']) ? 'btn-primary' : 'btn-outline-primary' }} px-4 py-2 fw-semibold">
            Métricas
        </a>
        <a href="{{ route('admin.skills.management') }}"
            class="btn {{ Route::is(['admin.skills.management']) ? 'btn-primary' : 'btn-outline-primary' }} px-4 py-2 fw-semibold">
            Habilidades
        </a>
        <a href="{{ route('admin.games.list') }}"
            class="btn {{ Route::is(['admin.games.list']) ? 'btn-primary' : 'btn-outline-primary' }} px-4 py-2 fw-semibold">
            Juegos
        </a>
    </div>

    <!-- Mobile Menu Toggle -->
    <a class="mobile_btn d-md-none" id="mobile_btn">
        <i class="fa fa-bars fs-4 text-dark"></i>
    </a>
    <!-- /Mobile Menu Toggle -->

    <!-- Header Right Menu -->
    <ul class="nav user-menu align-items-center gap-2">
        <!-- User Menu -->
        <li class="nav-item dropdown">
            <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                <span class="user-img rounded-circle overflow-hidden me-2">
                    <img src="{{ asset('common/favicon.png') }}" width="35" alt="Admin">
                </span>
                <span class="d-none d-md-inline fw-medium">Admin</span>
            </a>
            <div class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 py-2">
                <div class="user-header d-flex align-items-center px-3 mb-2 border-bottom">
                    <div class="avatar avatar-sm me-2">
                        <img src="{{ asset('common/favicon.png') }}" alt="Administrador" class="rounded-circle">
                    </div>
                    <div class="user-text">
                        <h6 class="mb-0 fw-semibold">Admin</h6>
                        <small class="text-muted">Administrador</small>
                    </div>
                </div>
                <a class="dropdown-item px-3 py-2" href="javascript:void(0)">Configuración</a>
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
                <a class="dropdown-item px-3 py-2" href="javascript:void(0)"
                    onclick="document.getElementById('logout-form').submit();">
                    Cerrar sesión
                </a>
            </div>
        </li>
        <!-- /User Menu -->
    </ul>
    <!-- /Header Right Menu -->

</div>

<!-- Optional Custom CSS -->
<style>
    .header {
        background-color: #ffffff;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        border-bottom: 1px solid #e5e5e5;
    }

    .header .btn-outline-primary {
        border-radius: 6px;
        transition: all 0.3s;
    }

    .header .btn-outline-primary:hover {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }

    .user-menu .dropdown-menu {
        min-width: 200px;
    }

    .user-menu .user-img img {
        object-fit: cover;
    }

    .user-menu .dropdown-item:hover {
        background-color: #f1f1f1;
    }
</style>
