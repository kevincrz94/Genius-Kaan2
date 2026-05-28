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
                Usa el correo y la contraseña asignados. El sistema abrirá el panel administrativo o los juegos
                Cognifit según el perfil autorizado.
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
                <div class="hero-note" style="color:#8a1f11;background:rgba(220,53,69,.08);">
                    {{ session('error') }}
                </div>
            @endif

            @if (session('success'))
                <div class="hero-note">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="hero-note" style="color:#8a1f11;background:rgba(220,53,69,.08);">
                    {{ $errors->first() }}
                </div>
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
                        <input id="password" class="input" type="password" name="password" required
                            autocomplete="current-password">
                    </div>
                </div>

                <div class="cta-row">
                    <button type="submit" class="btn btn-primary">Ingresar</button>
                    <a href="{{ route('home') }}" class="btn btn-secondary">Volver</a>
                </div>
            </form>
        </div>
    </section>
@endsection
