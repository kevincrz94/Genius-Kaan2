@extends('layouts.public')

@section('content')
    <section class="section">
        <div class="section-header">
            <div>
                <span class="eyebrow">Verificación inicial</span>
                <h2>Confirma tu perfil operativo.</h2>
            </div>
            <p class="section-copy">
                Antes de iniciar los simuladores, verifica tus datos personales y selecciona las capacidades que deseas reforzar.
            </p>
        </div>

        @if ($errors->any())
            <x-alert type="error">
                {{ $errors->first() }}
            </x-alert>
        @endif

        <form action="{{ route('user.onboarding.submit') }}" method="post" enctype="multipart/form-data" class="panel card onboarding-card">
            @csrf

            <div class="form-grid">
                <div class="field">
                    <label for="age">Edad</label>
                    <input id="age" class="input" type="number" name="age" min="18" max="120"
                        value="{{ old('age', $user->age) }}" required>
                </div>

                <div class="field">
                    <label for="gender">Sexo</label>
                    <select id="gender" name="gender" class="input" required>
                        <option value="">Selecciona</option>
                        <option value="male" @selected(old('gender', $user->gender) === 'male')>Masculino</option>
                        <option value="female" @selected(old('gender', $user->gender) === 'female')>Femenino</option>
                        <option value="other" @selected(old('gender', $user->gender) === 'other')>Otro</option>
                    </select>
                </div>

                <div class="field">
                    <label for="image">Foto de perfil</label>
                    <input id="image" class="input" type="file" name="image" accept="image/*">
                </div>

                <div class="field">
                    <label for="change_password">¿Deseas cambiar tu contraseña?</label>
                    <select id="change_password" name="change_password" class="input" required>
                        <option value="no" @selected(old('change_password', 'no') === 'no')>No</option>
                        <option value="yes" @selected(old('change_password') === 'yes')>Sí</option>
                    </select>
                </div>

                <div class="field password-field">
                    <label for="password">Nueva contraseña</label>
                    <input id="password" class="input" type="password" name="password" autocomplete="new-password">
                </div>

                <div class="field password-field">
                    <label for="password_confirmation">Confirmar contraseña</label>
                    <input id="password_confirmation" class="input" type="password" name="password_confirmation"
                        autocomplete="new-password">
                </div>
            </div>

            <div class="onboarding-block">
                <span class="eyebrow">Áreas de atención</span>
                <h3>Capacidades que te gustaría reforzar</h3>

                <div class="attention-grid">
                    @foreach ($attentionAreas as $area)
                        <label class="attention-option">
                            <input type="checkbox" name="attention_areas[]" value="{{ $area['key'] }}"
                                @checked(in_array($area['key'], old('attention_areas', collect($user->attention_areas ?? [])->pluck('key')->all()), true))>
                            <span class="attention-icon" aria-hidden="true">
                                @if (! empty($area['icon']))
                                    <img src="{{ $area['icon'] }}" alt="">
                                @else
                                    <span>{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($area['label'], 0, 1)) }}</span>
                                @endif
                            </span>
                            <span>{{ $area['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="cta-row">
                <button type="submit" class="btn btn-primary">Guardar y continuar</button>
            </div>
        </form>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selector = document.getElementById('change_password');
            const passwordFields = document.querySelectorAll('.password-field');

            const syncPasswordFields = () => {
                const shouldShow = selector.value === 'yes';

                passwordFields.forEach((field) => {
                    field.classList.toggle('is-hidden', !shouldShow);
                    field.querySelectorAll('input').forEach((input) => {
                        input.required = shouldShow;
                        if (!shouldShow) {
                            input.value = '';
                        }
                    });
                });
            };

            selector.addEventListener('change', syncPasswordFields);
            syncPasswordFields();
        });
    </script>
@endpush
