<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\CognifitService;
use App\Services\customBlock;
use CognifitSdk\Api\UserAccessToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Throwable;

class IndexController extends Controller
{
    public function home()
    {
        $pageTitle = 'Genius Kaan | Desarrollo cognitivo';

        $signals = [
            [
                'label' => 'Rutinas adaptativas',
                'value' => '12 min',
                'description' => 'Bloques cortos para sostener energia, foco y adherencia.',
            ],
            [
                'label' => 'Objetivos activos',
                'value' => '5 areas',
                'description' => 'Memoria, atencion, velocidad, razonamiento y flexibilidad.',
            ],
            [
                'label' => 'Seguimiento',
                'value' => '360deg',
                'description' => 'Vision compartida para familias, terapeutas y coordinadores.',
            ],
            [
                'label' => 'Entrenamiento',
                'value' => '1 panel',
                'description' => 'Evaluación, sesiones y reportes conectados en un mismo flujo.',
            ],
        ];

        $pillars = [
            [
                'title' => 'Atención ejecutiva',
                'description' => 'Rutinas para sostener foco, alternar tareas y reducir la fatiga cognitiva.',
                'accent' => '#ef8354',
            ],
            [
                'title' => 'Memoria funcional',
                'description' => 'Ejercicios para retener, manipular y recuperar informacion util en contexto.',
                'accent' => '#2a9d8f',
            ],
            [
                'title' => 'Lenguaje y comprension',
                'description' => 'Actividades enfocadas en expresion, procesamiento verbal y respuesta rapida.',
                'accent' => '#ffb703',
            ],
            [
                'title' => 'Razonamiento',
                'description' => 'Desafios para reconocer patrones, anticipar soluciones y tomar decisiones.',
                'accent' => '#577590',
            ],
            [
                'title' => 'Autorregulacion',
                'description' => 'Micro rutinas para bajar saturacion, ordenar tiempos y mejorar constancia.',
                'accent' => '#6d597a',
            ],
            [
                'title' => 'Velocidad de procesamiento',
                'description' => 'Estimulos medibles para acelerar lectura de senales y tiempos de respuesta.',
                'accent' => '#bc4749',
            ],
        ];

        $journey = [
            [
                'step' => '01',
                'title' => 'Evaluar el punto de partida',
                'description' => 'Capturamos fortalezas, alertas y metas para no entrenar a ciegas.',
            ],
            [
                'step' => '02',
                'title' => 'Diseñar sesiones útiles',
                'description' => 'Cada rutina responde a una necesidad concreta y a un perfil cognitivo real.',
            ],
            [
                'step' => '03',
                'title' => 'Seguir resultados',
                'description' => 'El progreso se revisa por habilidad, adherencia y consistencia en el tiempo.',
            ],
        ];

        $audiences = [
            [
                'title' => 'Infancia',
                'description' => 'Estimulos ludicos para lenguaje, memoria de trabajo y control inhibitorio.',
            ],
            [
                'title' => 'Adolescencia',
                'description' => 'Rutinas para organizacion, foco sostenido y rendimiento academico.',
            ],
            [
                'title' => 'Adultos',
                'description' => 'Programas de productividad mental, flexibilidad y toma de decisiones.',
            ],
            [
                'title' => 'Adulto mayor',
                'description' => 'Planes orientados a mantenimiento cognitivo, autonomia y bienestar diario.',
            ],
        ];

        return view('welcome', compact('pageTitle', 'signals', 'pillars', 'journey', 'audiences'));
    }

    public function showLogin()
    {
        if (session('operational_user_id')) {
            return redirect()->route('user.games');
        }

        $pageTitle = 'Genius Kaan | Acceso operativo';

        return view('user.login', compact('pageTitle'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('email', $credentials['email'])
            ->where('status', 1)
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->with('error', 'Credenciales inválidas o usuario inactivo.');
        }

        session()->put('operational_user_id', $user->id);

        return redirect()->route('user.games');
    }

    public function logout()
    {
        session()->forget('operational_user_id');

        return redirect()->route('user.login')->with('success', 'Sesión cerrada correctamente.');
    }

    public function games()
    {
        $user = $this->operationalUser();

        if (! $user) {
            return redirect()->route('user.login')->with('error', 'Inicia sesión para continuar.');
        }

        $cognifitError = null;

        if (! filled($user->cognifit_user_token)) {
            try {
                app(CognifitService::class)->registerUser($user, 'es');
                $user->refresh();
            } catch (Throwable $th) {
                $cognifitError = $th->getMessage();
            }
        }

        $pageTitle = 'Genius Kaan | Juegos operativos';
        $availableGames = $this->availableGames();

        return view('user.games', compact('pageTitle', 'user', 'availableGames', 'cognifitError'));
    }

    public function launcher(Request $request)
    {
        $pageTitle = 'Genius Kaan | Preparar sesión';

        $availableGames = $this->availableGames();

        $sessionDefaults = [
            'participant' => $request->string('participant')->trim()->value() ?: 'Paciente demo',
            'goal' => $request->string('goal')->trim()->value() ?: 'Fortalecer atencion y memoria funcional',
            'user_token' => $request->string('user_token')->trim()->value(),
            'game_key' => strtoupper($request->string('game_key')->trim()->value() ?: 'THE_BLUE_SHAPE'),
            'locale' => $request->string('locale')->trim()->value() ?: 'es',
        ];

        return view('launcher', compact('pageTitle', 'availableGames', 'sessionDefaults'));
    }

    public function startGame(Request $request)
    {
        $pageTitle = 'Genius Kaan | Sesion cognitiva';
        $cognifitUserToken = $request->string('user_token')->trim()->value();
        $accessToken = null;
        $launchError = null;

        if (filled($cognifitUserToken)) {
            try {
                $accessToken = $this->cognifitAccessToken($cognifitUserToken);
            } catch (Throwable $th) {
                $launchError = $th->getMessage();
            }
        }

        $launchConfig = [
            'participant' => $request->string('participant')->trim()->value() ?: 'Paciente',
            'goal' => $request->string('goal')->trim()->value() ?: 'Entrenamiento cognitivo personalizado',
            'gameKey' => strtoupper($request->string('game_key')->trim()->value()),
            'userToken' => $accessToken,
            'locale' => $request->string('locale')->trim()->value() ?: 'es',
            'image' => $request->string('image')->trim()->value(),
            'clientId' => config('services.cognifit.client_id') ?: '2cc41d68527b1b5eb49ee8ce8d802468',
            'sdkVersion' => $this->cognifitSdkVersion(),
            'launchError' => $launchError,
        ];

        return view('index', compact('pageTitle', 'launchConfig'));
    }

    private function cognifitAccessToken(string $cognifitUserToken): ?string
    {
        if (! filled(config('services.cognifit.client_id')) || ! filled(config('services.cognifit.client_secret'))) {
            throw new \RuntimeException('Faltan credenciales de Cognifit en .env.');
        }

        $api = new UserAccessToken(
            config('services.cognifit.client_id'),
            config('services.cognifit.client_secret')
        );

        $response = $api->issue($cognifitUserToken);

        if ($response->hasError()) {
            throw new \RuntimeException('Cognifit no pudo emitir access token para el usuario.');
        }

        $accessToken = $response->get('access_token');

        if (! filled($accessToken)) {
            throw new \RuntimeException('Cognifit no devolvio access token.');
        }

        return $accessToken;
    }

    private function cognifitSdkVersion(): ?string
    {
        try {
            $response = Http::timeout(8)
                ->acceptJson()
                ->get(rtrim(config('services.cognifit.base_url'), '/').'/description/versions/sdkjs', [
                    'v' => '2.0',
                ]);

            if (! $response->successful()) {
                return null;
            }

            return $this->extractCognifitVersion($response->json() ?? trim($response->body()));
        } catch (Throwable $th) {
            return null;
        }
    }

    private function extractCognifitVersion(mixed $payload): ?string
    {
        if (is_string($payload)) {
            $version = trim($payload, " \t\n\r\0\x0B\"'");

            return $version !== '' ? $version : null;
        }

        if (is_array($payload)) {
            foreach (['version', 'sdkjs', 'sdkJs', 'current', 'latest'] as $key) {
                if (array_key_exists($key, $payload)) {
                    $version = $this->extractCognifitVersion($payload[$key]);

                    if ($version) {
                        return $version;
                    }
                }
            }

            foreach ($payload as $value) {
                $version = $this->extractCognifitVersion($value);

                if ($version) {
                    return $version;
                }
            }
        }

        return null;
    }

    private function operationalUser(): ?User
    {
        $userId = session('operational_user_id');

        if (! $userId) {
            return null;
        }

        return User::query()
            ->with(['securityUnit', 'operationalGroup'])
            ->where('status', 1)
            ->find($userId);
    }

    private function availableGames(): array
    {
        $games = customBlock::getBrainGamesData('programs/tasks', 'GET')
            ->map(function ($game): array {
                $title = $game->assets->titles->es
                    ?? $game->assets->titles->en
                    ?? customBlock::processStringNames((string) ($game->key ?? 'Juego'));

                $description = $game->assets->descriptions->es
                    ?? $game->assets->descriptions->en
                    ?? 'Entrenamiento cognitivo Cognifit.';

                return [
                    'key' => (string) ($game->key ?? ''),
                    'title' => $title,
                    'focus' => $description,
                    'image' => $game->assets->images->icon ?? null,
                    'skills' => collect($game->skills ?? [])
                        ->map(fn ($skill) => customBlock::processStringNames((string) $skill))
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn (array $game): bool => $game['key'] !== '')
            ->sortBy('title')
            ->values()
            ->all();

        if ($games !== []) {
            return $games;
        }

        return [
            [
                'key' => 'THE_BLUE_SHAPE',
                'title' => 'The Blue Shape',
                'focus' => 'Atención selectiva y velocidad de respuesta.',
            ],
            [
                'key' => 'MAHJONG',
                'title' => 'Mahjong',
                'focus' => 'Memoria visual, estrategia y reconocimiento de patrones.',
            ],
            [
                'key' => 'PIT_STOP',
                'title' => 'Pit Stop',
                'focus' => 'Planificación, alternancia mental y control ejecutivo.',
            ],
            [
                'key' => 'FROGGY_CROSSING',
                'title' => 'Froggy Crossing',
                'focus' => 'Coordinación, anticipación y gestión de impulsos.',
            ],
        ];
    }
}
