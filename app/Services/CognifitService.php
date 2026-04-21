<?php

namespace App\Services;

use App\Models\User;
use CognifitSdk\Api\UserAccount;
use CognifitSdk\Api\UserActivity;
use CognifitSdk\Lib\UserData;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use RuntimeException;

class CognifitService
{
    public function configured(): bool
    {
        return filled($this->clientId()) && filled($this->clientSecret());
    }

    public function clientId(): ?string
    {
        return config('services.cognifit.client_id');
    }

    public function hash(): ?string
    {
        return config('services.cognifit.hash');
    }

    public function launchUrl(): ?string
    {
        return config('services.cognifit.launch_url');
    }

    public function registerUser(User $user, string $locale = 'es', ?string $password = null): string
    {
        $this->ensureConfigured();

        if (filled($user->cognifit_user_token)) {
            return $user->cognifit_user_token;
        }

        $api = new UserAccount($this->clientId(), $this->clientSecret());
        $response = $api->registration(new UserData([
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_birthday' => $this->birthdayFor($user),
            'user_locale' => $locale,
            'user_password' => $password ?: 'Gk@'.Str::random(24),
        ]));

        if ($response->hasError()) {
            throw new RuntimeException('Cognifit rechazo el registro del usuario.');
        }

        $token = $response->get('user_token');

        if (! filled($token)) {
            throw new RuntimeException('Cognifit no devolvio user_token.');
        }

        $user->forceFill([
            'cognifit_user_token' => $token,
            'cognifit_locale' => $locale,
            'cognifit_registered_at' => now(),
        ])->save();

        return $token;
    }

    public function updateLocale(User $user, string $locale): void
    {
        $this->ensureConfigured();
        $this->ensureCognifitUser($user);

        $api = new UserAccount($this->clientId(), $this->clientSecret());
        $response = $api->update($user->cognifit_user_token, new UserData([
            'user_locale' => $locale,
        ]));

        if ($response->hasError()) {
            throw new RuntimeException('No fue posible actualizar el idioma en Cognifit.');
        }

        $user->forceFill(['cognifit_locale' => $locale])->save();
    }

    public function historicalScores(User $user): array
    {
        $this->ensureConfigured();
        $this->ensureCognifitUser($user);

        $api = new UserActivity($this->clientId(), $this->clientSecret());
        $response = $api->getHistoricalScoreAndSkills($user->cognifit_user_token);

        if ($response->hasError()) {
            throw new RuntimeException('No fue posible consultar puntajes de Cognifit.');
        }

        return (array) $response->getData();
    }

    public function playedGames(User $user): array
    {
        $this->ensureConfigured();
        $this->ensureCognifitUser($user);

        $api = new UserActivity($this->clientId(), $this->clientSecret());
        $response = $api->getPlayedGames($user->cognifit_user_token);

        if ($response->hasError()) {
            throw new RuntimeException('No fue posible consultar juegos de Cognifit.');
        }

        return (array) $response->getData();
    }

    public function launchPayload(User $user, string $gameKey, ?string $goal = null): array
    {
        $this->ensureConfigured();
        $this->ensureCognifitUser($user);

        $normalizedGameKey = Str::upper($gameKey);

        return [
            'gameKey' => $normalizedGameKey,
            'userToken' => $user->cognifit_user_token,
            'clientId' => $this->clientId(),
            'launchingURL' => route('start.game', [
                'participant' => $user->name,
                'goal' => $goal ?: 'Entrenamiento cognitivo personalizado',
                'locale' => $user->cognifit_locale ?: 'es',
                'user_token' => $user->cognifit_user_token,
                'game_key' => $normalizedGameKey,
            ]),
        ];
    }

    private function ensureConfigured(): void
    {
        if (! $this->configured()) {
            throw new RuntimeException('Faltan credenciales de Cognifit en .env.');
        }
    }

    private function ensureCognifitUser(User $user): void
    {
        if (! filled($user->cognifit_user_token)) {
            throw new RuntimeException('El usuario aun no esta registrado en Cognifit.');
        }
    }

    private function birthdayFor(User $user): string
    {
        $age = $user->age ?: 18;

        return Carbon::now()->subYears($age)->startOfYear()->format('Y-m-d');
    }

    private function clientSecret(): ?string
    {
        return config('services.cognifit.client_secret');
    }
}
