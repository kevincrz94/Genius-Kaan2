@extends('layouts.public')

@section('content')
    <section class="hero">
        <div class="panel hero-copy">
            <span class="eyebrow">Acceso institucional</span>
            <h1>
                Ingresa al
                <span class="highlight">entorno Genius Kaan</span>.
            </h1>
            <p class="lead">
                Usa el correo y la contraseña asignados. El sistema abrirá el panel administrativo o los módulos
                CogniFit según el perfil autorizado.
            </p>

            <div class="badge-row">
                <span class="soft-chip">Superusuario</span>
                <span class="soft-chip">Administrador</span>
                <span class="soft-chip">Elemento operativo</span>
            </div>
        </div>

        <div class="panel card session-card">
            <span class="eyebrow">Iniciar sesión</span>
            <h2>Acceso unificado.</h2>

            @if (session('error'))
                <x-alert type="error">
                    {{ session('error') }}
                </x-alert>
            @endif

            @if (session('success'))
                <x-alert type="success">
                    {{ session('success') }}
                </x-alert>
            @endif

            @if ($errors->any())
                <x-alert type="error">
                    {{ $errors->first() }}
                </x-alert>
            @endif

            <form method="post" action="{{ route('user.login.submit') }}">
                @csrf
                <div class="form-grid">
                    <div class="field field-full">
                        <label for="email">Correo</label>
                        <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required
                            autocomplete="email">
                    </div>

                    <div class="field field-full">
                        <label for="password">Contraseña</label>
                        <div class="password-field">
                            <input id="password" class="input" type="password" name="password" required
                                autocomplete="current-password">
                            <button type="button" class="password-toggle" data-password-toggle="password"
                                aria-label="Mostrar contraseña" aria-pressed="false">
                                Ver
                            </button>
                        </div>
                    </div>
                </div>

                <div class="cta-row">
                    <button type="submit" class="btn btn-primary">Ingresar</button>
                    <a href="{{ route('home') }}" class="btn btn-secondary">Volver</a>
                </div>

                <p class="helper-copy access-help">
                    ¿Sin acceso? Solicita validación a tu mando inmediato.
                </p>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('[data-password-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const input = document.getElementById(button.dataset.passwordToggle);
                if (!input) {
                    return;
                }

                const isVisible = input.type === 'text';
                input.type = isVisible ? 'password' : 'text';
                button.textContent = isVisible ? 'Ver' : 'Ocultar';
                button.setAttribute('aria-label', isVisible ? 'Mostrar contraseña' : 'Ocultar contraseña');
                button.setAttribute('aria-pressed', String(!isVisible));
            });
        });
    </script>
@endpush
