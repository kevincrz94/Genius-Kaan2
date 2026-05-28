<?php

namespace App\Http\Controllers;

use App\Models\CognitiveSession;
use App\Models\CognitiveSkillScore;
use App\Models\User;
use App\Services\CognifitService;
use App\Services\customBlock;
use App\Services\FileHelper;
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
        if (session('admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        if (session('operational_user_id')) {
            return redirect()->route('user.games');
        }

        $pageTitle = 'Genius Kaan | Acceso institucional';

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

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $request->session()->regenerate();
            session()->put('auth_user_id', $user->id);

            if ($user->isAdmin()) {
                session()->put('admin_id', $user->id);
                session()->put('admin_role', $user->role);
                session()->forget('operational_user_id');

                return redirect()->route('admin.dashboard');
            }

            session()->put('operational_user_id', $user->id);
            session()->forget('admin_id');
            session()->forget('admin_role');

            if (! filled($user->onboarding_completed_at)) {
                return redirect()->route('user.onboarding');
            }

            return redirect()->route('user.games');
        }

        if ($this->matchesConfiguredAdmin($credentials['email'], $credentials['password'])) {
            $request->session()->regenerate();
            session()->put('auth_user_id', 'configured-super-admin');
            session()->put('admin_id', 'configured-super-admin');
            session()->put('admin_role', 'super_admin');
            session()->forget('operational_user_id');

            return redirect()->route('admin.dashboard');
        }

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return redirect()
                ->back()
                ->withInput($request->only('email'))
                ->with('error', 'Credenciales inválidas o usuario inactivo.');
        }

        session()->put('operational_user_id', $user->id);

        if (! filled($user->onboarding_completed_at)) {
            return redirect()->route('user.onboarding');
        }

        return redirect()->route('user.games');
    }

    public function showOnboarding()
    {
        $user = $this->operationalUser();

        if (! $user) {
            return redirect()->route('user.login')->with('error', 'Inicia sesión para continuar.');
        }

        if (filled($user->onboarding_completed_at)) {
            return redirect()->route('user.games');
        }

        $pageTitle = 'Genius Kaan | Verificación inicial';
        $attentionAreas = $this->attentionAreaOptions();

        return view('user.onboarding', compact('pageTitle', 'user', 'attentionAreas'));
    }

    public function completeOnboarding(Request $request)
    {
        $user = $this->operationalUser();

        if (! $user) {
            return redirect()->route('user.login')->with('error', 'Inicia sesión para continuar.');
        }

        $validated = $request->validate([
            'change_password' => ['required', 'in:yes,no'],
            'password' => ['nullable', 'required_if:change_password,yes', 'string', 'min:8', 'confirmed'],
            'age' => ['required', 'integer', 'min:18', 'max:120'],
            'gender' => ['required', 'in:male,female,other'],
            'image' => ['nullable', 'image', 'max:4096'],
            'attention_areas' => ['nullable', 'array'],
            'attention_areas.*' => ['string', 'max:120'],
        ]);

        $attentionOptions = collect($this->attentionAreaOptions())->keyBy('key');
        $selectedAreas = collect($validated['attention_areas'] ?? [])
            ->filter(fn (string $key) => $attentionOptions->has($key))
            ->map(fn (string $key) => [
                'key' => $key,
                'name' => $attentionOptions[$key]['label'],
            ])
            ->values()
            ->all();

        $payload = [
            'age' => $validated['age'],
            'gender' => $validated['gender'],
            'attention_areas' => $selectedAreas,
            'onboarding_completed_at' => now(),
        ];

        if (($validated['change_password'] ?? 'no') === 'yes') {
            $payload['password'] = $validated['password'];
            $payload['password_changed_at'] = now();
        }

        if ($request->hasFile('image')) {
            FileHelper::createDirectory(public_path('UserImages'));
            $payload['image'] = FileHelper::uploadImage($request->file('image'), 'UserImages');
        }

        $user->update($payload);

        return redirect()->route('user.games')->with('success', 'Perfil verificado correctamente.');
    }

    public function logout()
    {
        session()->forget('auth_user_id');
        session()->forget('operational_user_id');
        session()->forget('admin_id');
        session()->forget('admin_role');

        return redirect()->route('user.login')->with('success', 'Sesión cerrada correctamente.');
    }

    public function games()
    {
        $user = $this->operationalUser();

        if (! $user) {
            return redirect()->route('user.login')->with('error', 'Inicia sesión para continuar.');
        }

        if (! filled($user->onboarding_completed_at)) {
            return redirect()->route('user.onboarding');
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

        $pageTitle = 'Genius Kaan | Módulos operativos';
        $availableGames = $this->availableGames();
        $skillFilters = $this->skillFilters($availableGames);

        return view('user.games', compact('pageTitle', 'user', 'availableGames', 'skillFilters', 'cognifitError'));
    }

    public function profile()
    {
        $user = $this->operationalUser();

        if (! $user) {
            return redirect()->route('user.login')->with('error', 'Inicia sesión para continuar.');
        }

        $pageTitle = 'Genius Kaan | Perfil operativo';
        $attentionAreas = collect($user->attention_areas ?? [])
            ->map(fn ($area) => is_array($area) ? ($area['name'] ?? $area['label'] ?? $area['key'] ?? null) : $area)
            ->filter()
            ->values()
            ->all();

        $completedSessions = $user->cognitiveSessions()
            ->where('status', 'completed');

        $stats = [
            'completed_sessions' => (clone $completedSessions)->count(),
            'total_minutes' => (int) (clone $completedSessions)->sum('duration_minutes'),
            'average_score' => round((float) ((clone $completedSessions)->whereNotNull('score')->avg('score') ?? 0), 1),
            'last_session_at' => optional((clone $completedSessions)->latest('completed_at')->first()?->completed_at)->format('d/m/Y H:i'),
        ];

        $latestSessions = $user->cognitiveSessions()
            ->where('status', 'completed')
            ->latest('completed_at')
            ->take(5)
            ->get();

        $skillStats = $user->cognitiveSkillScores()
            ->latest('measured_at')
            ->take(8)
            ->get()
            ->map(fn ($score) => [
                'name' => $this->translateCognitiveSkill($score->name),
                'score' => round((float) $score->score, 1),
                'trend' => $score->trend,
                'measured_at' => optional($score->measured_at)->format('d/m/Y'),
            ]);

        return view('user.profile', compact('pageTitle', 'user', 'attentionAreas', 'stats', 'latestSessions', 'skillStats'));
    }

    public function launcher(Request $request)
    {
        $pageTitle = 'Genius Kaan | Preparar sesión';

        $availableGames = $this->availableGames();

        $sessionDefaults = [
            'participant' => $request->string('participant')->trim()->value() ?: 'Elemento demo',
            'goal' => $request->string('goal')->trim()->value() ?: 'Fortalecer atención y memoria funcional',
            'user_token' => $request->string('user_token')->trim()->value(),
            'game_key' => strtoupper($request->string('game_key')->trim()->value() ?: 'THE_BLUE_SHAPE'),
            'locale' => $request->string('locale')->trim()->value() ?: 'es',
        ];

        return view('launcher', compact('pageTitle', 'availableGames', 'sessionDefaults'));
    }

    public function startGame(Request $request)
    {
        $pageTitle = 'Genius Kaan | Sesión cognitiva';
        $cognifitUserToken = $request->string('user_token')->trim()->value();
        $user = filled($cognifitUserToken)
            ? User::query()->where('cognifit_user_token', $cognifitUserToken)->first()
            : null;
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
            'participant' => $request->string('participant')->trim()->value() ?: 'Elemento operativo',
            'goal' => $request->string('goal')->trim()->value() ?: 'Entrenamiento cognitivo operativo',
            'gameKey' => strtoupper($request->string('game_key')->trim()->value()),
            'userToken' => $accessToken,
            'locale' => $request->string('locale')->trim()->value() ?: 'es',
            'image' => $request->string('image')->trim()->value(),
            'clientId' => config('services.cognifit.client_id'),
            'sdkVersion' => $this->cognifitSdkVersion(),
            'appType' => in_array($request->string('app_type')->trim()->value(), ['web', 'app'], true)
                ? $request->string('app_type')->trim()->value()
                : 'web',
            'syncUrl' => $user ? route('cognifit.session.sync', $user) : null,
            'launchError' => $launchError,
        ];

        return view('index', compact('pageTitle', 'launchConfig'));
    }

    public function syncCognifitSession(Request $request, User $user)
    {
        if ((int) session('operational_user_id') !== (int) $user->id) {
            abort(403);
        }

        $validated = $request->validate([
            'game_key' => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:completed,aborted'],
            'mode' => ['nullable', 'string', 'max:80'],
        ]);

        $playedGames = [];
        $scores = [];

        if ($validated['status'] === 'completed') {
            try {
                $cognifit = app(CognifitService::class);
                $playedGames = $cognifit->playedGames($user);
                $scores = $cognifit->historicalScores($user);
            } catch (Throwable $th) {
                report($th);
            }
        }

        $latestGame = $this->latestPlayedGame($playedGames, $validated['game_key']);

        $session = CognitiveSession::create([
            'user_id' => $user->id,
            'area' => 'cognifit',
            'game_key' => $validated['game_key'],
            'duration_minutes' => $this->durationFromGame($latestGame),
            'status' => $validated['status'] === 'completed' ? 'completed' : 'cancelled',
            'score' => $this->scoreFromGame($latestGame),
            'started_at' => now(),
            'completed_at' => now(),
            'metadata' => [
                'mode' => $validated['mode'] ?? 'gameMode',
                'cognifit_game' => $latestGame,
                'synced_at' => now()->toISOString(),
            ],
        ]);

        $storedScores = $this->storeCognifitSkillScores($user, $session, $scores);

        return response()->json([
            'status' => true,
            'message' => $validated['status'] === 'completed'
                ? 'Resultados sincronizados.'
                : 'Actividad cancelada registrada.',
            'session_id' => $session->id,
            'score' => $session->score,
            'skill_scores' => $storedScores,
            'played_games' => count((array) ($playedGames['historicalPlayedGames'] ?? [])),
        ]);
    }

    private function cognifitAccessToken(string $cognifitUserToken): ?string
    {
        if (! filled(config('services.cognifit.client_id')) || ! filled(config('services.cognifit.client_secret'))) {
            throw new \RuntimeException('Faltan credenciales de CogniFit en .env.');
        }

        $api = new UserAccessToken(
            config('services.cognifit.client_id'),
            config('services.cognifit.client_secret')
        );

        $response = $api->issue($cognifitUserToken);

        if ($response->hasError()) {
            throw new \RuntimeException('CogniFit no pudo emitir access token para el usuario.');
        }

        $accessToken = $response->get('access_token');

        if (! filled($accessToken)) {
            throw new \RuntimeException('CogniFit no devolvió access token.');
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

    private function latestPlayedGame(array $playedGames, string $gameKey): ?array
    {
        $games = collect($playedGames['historicalPlayedGames'] ?? [])
            ->map(fn ($game) => (array) $game)
            ->filter(fn (array $game) => strtoupper((string) ($game['key'] ?? $game['game_key'] ?? '')) === strtoupper($gameKey))
            ->sortByDesc(fn (array $game) => $game['time'] ?? $game['date'] ?? $game['played_at'] ?? '')
            ->values();

        return $games->first();
    }

    private function scoreFromGame(?array $game): ?float
    {
        if (! $game) {
            return null;
        }

        foreach (['score', 'percentage', 'accuracy', 'result'] as $key) {
            if (isset($game[$key]) && is_numeric($game[$key])) {
                return round(min(100, max(0, (float) $game[$key])), 2);
            }
        }

        return null;
    }

    private function durationFromGame(?array $game): int
    {
        if (! $game) {
            return 0;
        }

        foreach (['duration_minutes', 'duration', 'time_spent'] as $key) {
            if (isset($game[$key]) && is_numeric($game[$key])) {
                $duration = (float) $game[$key];

                return (int) max(0, $duration > 180 ? round($duration / 60) : round($duration));
            }
        }

        return 0;
    }

    private function storeCognifitSkillScores(User $user, CognitiveSession $session, array $scores): int
    {
        $skillScores = collect($this->extractSkillScores($scores));
        $stored = 0;

        foreach ($skillScores as $skill) {
            $name = trim((string) ($skill['name'] ?? $skill['key'] ?? ''));

            if ($name === '' || ! isset($skill['score']) || ! is_numeric($skill['score'])) {
                continue;
            }

            CognitiveSkillScore::create([
                'user_id' => $user->id,
                'cognitive_session_id' => $session->id,
                'name' => $name,
                'score' => round(min(100, max(0, (float) $skill['score'])), 2),
                'trend' => $skill['trend'] ?? 'stable',
                'measured_at' => now(),
            ]);

            $stored++;
        }

        return $stored;
    }

    private function extractSkillScores(mixed $payload): array
    {
        if (! is_array($payload)) {
            return [];
        }

        $scores = [];

        foreach ($payload as $key => $value) {
            if (is_array($value)) {
                $name = $value['name'] ?? $value['key'] ?? (is_string($key) ? $key : null);
                $score = $value['score'] ?? $value['value'] ?? $value['percentage'] ?? null;

                if ($name && is_numeric($score)) {
                    $scores[] = [
                        'name' => $name,
                        'score' => $score,
                        'trend' => $value['trend'] ?? 'stable',
                    ];
                }

                $scores = array_merge($scores, $this->extractSkillScores($value));
            }
        }

        return $scores;
    }

    private function matchesConfiguredAdmin(string $email, string $password): bool
    {
        return filled(config('admin.email'))
            && filled(config('admin.password'))
            && hash_equals((string) config('admin.email'), $email)
            && hash_equals((string) config('admin.password'), $password);
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
        $skillCatalog = $this->cognifitSkillCatalog();

        $games = customBlock::getBrainGamesData('programs/tasks', 'GET')
            ->map(function ($game) use ($skillCatalog): array {
                $title = $game->assets->titles->es
                    ?? $game->assets->titles->en
                    ?? customBlock::processStringNames((string) ($game->key ?? 'Módulo'));

                $description = $game->assets->descriptions->es
                    ?? $game->assets->descriptions->en
                    ?? 'Entrenamiento cognitivo CogniFit.';

                return [
                    'key' => (string) ($game->key ?? ''),
                    'title' => $title,
                    'focus' => $description,
                    'image' => $game->assets->images->icon ?? null,
                    'skill_keys' => collect($game->skills ?? [])
                        ->map(fn ($skill) => (string) $skill)
                        ->filter()
                        ->values()
                        ->all(),
                    'skills' => collect($game->skills ?? [])
                        ->map(fn ($skill) => $this->cognifitSkillLabel((string) $skill, $skillCatalog))
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
                'skill_keys' => [],
                'skills' => [],
            ],
            [
                'key' => 'MAHJONG',
                'title' => 'Mahjong',
                'focus' => 'Memoria visual, estrategia y reconocimiento de patrones.',
                'skill_keys' => [],
                'skills' => [],
            ],
            [
                'key' => 'PIT_STOP',
                'title' => 'Pit Stop',
                'focus' => 'Planificación, alternancia mental y control ejecutivo.',
                'skill_keys' => [],
                'skills' => [],
            ],
            [
                'key' => 'FROGGY_CROSSING',
                'title' => 'Froggy Crossing',
                'focus' => 'Coordinación, anticipación y gestión de impulsos.',
                'skill_keys' => [],
                'skills' => [],
            ],
        ];
    }

    private function skillFilters(array $games): array
    {
        $usedSkillKeys = collect($games)
            ->flatMap(fn (array $game) => $game['skill_keys'] ?? [])
            ->filter()
            ->unique()
            ->values();

        if ($usedSkillKeys->isEmpty()) {
            return [];
        }

        $skillCatalog = $this->cognifitSkillCatalog();

        return $usedSkillKeys
            ->map(function (string $skillKey) use ($skillCatalog): array {
                $skill = $skillCatalog->get($skillKey);

                return [
                    'key' => $skillKey,
                    'label' => $this->cognifitSkillLabel($skillKey, $skillCatalog),
                    'icon' => $skill->assets->images->whiteIcon
                        ?? $skill->assets->images->icon
                        ?? null,
                ];
            })
            ->sortBy('label')
            ->values()
            ->all();
    }

    private function attentionAreaOptions(): array
    {
        $skills = customBlock::getSDKData('skills', 'GET')
            ->map(function ($skill): array {
                $key = (string) ($skill->key ?? '');

                return [
                    'key' => $key,
                    'label' => $this->translateCognitiveSkill(
                        $key,
                        $skill->assets->titles->es ?? $skill->assets->titles->en ?? null
                    ),
                    'icon' => $skill->assets->images->whiteIcon
                        ?? $skill->assets->images->icon
                        ?? null,
                ];
            })
            ->filter(fn (array $skill) => $skill['key'] !== '')
            ->sortBy('label')
            ->values()
            ->all();

        if ($skills !== []) {
            return $skills;
        }

        return [
            ['key' => 'ATTENTION', 'label' => 'Atención sostenida', 'icon' => null],
            ['key' => 'REACTION_TIME', 'label' => 'Tiempo de reacción', 'icon' => null],
            ['key' => 'WORKING_MEMORY', 'label' => 'Memoria de trabajo', 'icon' => null],
            ['key' => 'INHIBITION', 'label' => 'Control inhibitorio', 'icon' => null],
            ['key' => 'PLANNING', 'label' => 'Planeación', 'icon' => null],
            ['key' => 'VISUAL_PERCEPTION', 'label' => 'Percepción visual', 'icon' => null],
        ];
    }

    private function cognifitSkillCatalog()
    {
        return customBlock::getSDKData('skills', 'GET')
            ->keyBy(fn ($skill) => (string) ($skill->key ?? ''));
    }

    private function cognifitSkillLabel(string $skillKey, $skillCatalog): string
    {
        $skill = $skillCatalog->get($skillKey);

        return $this->translateCognitiveSkill(
            $skillKey,
            $skill->assets->titles->es ?? $skill->assets->titles->en ?? null
        );
    }

    private function translateCognitiveSkill(string $key, ?string $label = null): string
    {
        $translations = [
            'ATTENTION' => 'Atención',
            'FOCUSED_ATTENTION' => 'Atención focalizada',
            'DIVIDED_ATTENTION' => 'Atención dividida',
            'SUSTAINED_ATTENTION' => 'Atención sostenida',
            'SELECTIVE_ATTENTION' => 'Atención selectiva',
            'WORKING_MEMORY' => 'Memoria de trabajo',
            'SHORT_TERM_MEMORY' => 'Memoria a corto plazo',
            'VISUAL_SHORT_TERM_MEMORY' => 'Memoria visual a corto plazo',
            'AUDITORY_SHORT_TERM_MEMORY' => 'Memoria auditiva a corto plazo',
            'CONTEXTUAL_MEMORY' => 'Memoria contextual',
            'NON_VERBAL_MEMORY' => 'Memoria no verbal',
            'NAMING' => 'Denominación',
            'PROCESSING_SPEED' => 'Velocidad de procesamiento',
            'RESPONSE_TIME' => 'Tiempo de respuesta',
            'REACTION_TIME' => 'Tiempo de reacción',
            'INHIBITION' => 'Control inhibitorio',
            'PLANNING' => 'Planeación',
            'COORDINATION' => 'Coordinación',
            'EYE_HAND_COORDINATION' => 'Coordinación ojo-mano',
            'UPDATING' => 'Actualización',
            'SHIFTING' => 'Flexibilidad cognitiva',
            'COGNITIVE_FLEXIBILITY' => 'Flexibilidad cognitiva',
            'VISUAL_PERCEPTION' => 'Percepción visual',
            'SPATIAL_PERCEPTION' => 'Percepción espacial',
            'VISUAL_SCANNING' => 'Escaneo visual',
            'RECOGNITION' => 'Reconocimiento',
            'ESTIMATION' => 'Estimación',
            'MONITORING' => 'Monitoreo',
            'REASONING' => 'Razonamiento',
            'LOGICAL_REASONING' => 'Razonamiento lógico',
        ];

        $normalizedKey = strtoupper(trim($key));

        if (isset($translations[$normalizedKey])) {
            return $translations[$normalizedKey];
        }

        $normalizedLabel = strtoupper(str_replace([' ', '-'], '_', trim((string) $label)));

        if ($normalizedLabel !== '' && isset($translations[$normalizedLabel])) {
            return $translations[$normalizedLabel];
        }

        return $label ?: customBlock::processStringNames($key);
    }
}
