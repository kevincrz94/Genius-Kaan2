<?php

namespace App\Http\Controllers;

use App\Exports\userExport;
use App\Imports\UserImport;
use App\Models\AssignmentArea;
use App\Models\CognitiveSession;
use App\Models\CognitiveSkillScore;
use App\Models\OperationalAlert;
use App\Models\OperationalGroup;
use App\Models\OperationalMetricSnapshot;
use App\Models\OperationalRank;
use App\Models\SecurityUnit;
use App\Models\User;
use App\Services\customBlock;
use App\Services\FileHelper;
use App\Services\StringHelper;
use Carbon\Carbon;
use CognifitSdk\Api\UserAccount;
use CognifitSdk\Api\UserActivity;
use CognifitSdk\Lib\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
// Top par
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class AdminController extends Controller
{
    protected $database;

    protected $auth;

    public function __construct()
    {
        //
    }

    public function login()
    {
        return redirect()->route('user.login');

    }

    public function loginCheck(Request $request)
    {
        return redirect()->route('user.login')->with('error', 'Usa el acceso institucional unificado.');
    }

    public function logout()
    {
        session()->forget('auth_user_id');
        session()->forget('operational_user_id');
        session()->forget('admin_id');
        session()->forget('admin_role');

        return redirect()->route('user.login')->with('success', 'Sesión cerrada correctamente.');
    }

    public function dashboard()
    {
        $title = 'Panel de mando';

        $list = User::query()
            ->where('role', 'user')
            ->latest('id')
            ->get();

        $totalElements = $list->count();
        $syncedTokens = $list->filter(fn (User $user) => filled($user->cognifit_user_token))->count();
        $syncPercentage = $totalElements > 0 ? (int) round(($syncedTokens / $totalElements) * 100) : 0;

        $globalIndex = Schema::hasTable('operational_metric_snapshots')
            ? OperationalMetricSnapshot::query()->avg('score')
            : null;
        $globalIndex ??= Schema::hasTable('cognitive_skill_scores')
            ? CognitiveSkillScore::query()->avg('score')
            : null;
        $globalIndex ??= Schema::hasTable('cognitive_sessions')
            ? CognitiveSession::query()->whereNotNull('score')->avg('score')
            : null;
        $globalIndex ??= 0;
        $globalIndex = (int) round((float) $globalIndex);

        $activeAlertCount = Schema::hasTable('operational_alerts')
            ? OperationalAlert::query()
                ->whereNull('resolved_at')
                ->count()
            : 0;

        $lowMetricUsers = Schema::hasTable('operational_metric_snapshots')
            ? OperationalMetricSnapshot::query()
                ->whereNotNull('user_id')
                ->where(function ($query) {
                    $query->where('score', '<', 60)
                        ->orWhereIn('level', ['critico', 'crítico', 'refuerzo', 'alerta']);
                })
                ->distinct('user_id')
                ->count('user_id')
            : 0;

        $alertCount = max($activeAlertCount, $lowMetricUsers);
        $alertProgress = $totalElements > 0 ? min(100, (int) round(($alertCount / $totalElements) * 100)) : 0;

        $unitCount = Schema::hasTable('security_units') ? SecurityUnit::query()->count() : 0;
        $groupCount = Schema::hasTable('operational_groups') ? OperationalGroup::query()->count() : 0;
        $recentSessions = Schema::hasTable('cognitive_sessions')
            ? CognitiveSession::query()
                ->whereNotNull('completed_at')
                ->where('completed_at', '>=', now()->subDays(30))
                ->count()
            : 0;

        return view('admin.index', compact(
            'title',
            'list',
            'alertCount',
            'alertProgress',
            'globalIndex',
            'syncPercentage',
            'syncedTokens',
            'totalElements',
            'unitCount',
            'groupCount',
            'recentSessions'
        ));
    }

    public function skillManagement()
    {
        $title = 'Capacidades cognitivas';

        $endPoint = 'skills';
        $method = 'GET';

        $list = customBlock::getSDKData($endPoint, $method);

        $data = compact('title', 'list');

        return view('admin.categories.index')->with($data);
    }

    public function userManagement()
    {
        $title = 'Gestión de elementos';
        $catalogs = $this->operationalCatalogs();

        $list = User::query()
            ->with(['securityUnit', 'operationalGroup'])
            ->latest('id')
            ->get()
            ->map(fn (User $user) => $this->userPayload($user));

        $data = compact('title', 'list') + $catalogs;

        return view('admin.users.index')->with($data);
    }

    public function userProfile($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'Elemento no encontrado']);
        }

        $info = $this->userPayload($user);
        $goals = $this->normalizedUserInterest($info, 'goals');
        $areas = $this->normalizedUserInterest($info, 'areas');
        $gameData = null;
        $playedGames = [];
        $brainGames = [];
        $brainGameTitles = [];
        $localSessions = CognitiveSession::query()
            ->where('user_id', $user->id)
            ->latest('completed_at')
            ->latest('id')
            ->limit(10)
            ->get();

        $getToken = $user->cognifit_user_token;
        if ($getToken && $getToken != '-') {
            $api = new \CognifitSdk\Api\UserActivity(
                config('services.cognifit.client_id'),
                config('services.cognifit.client_secret')
            );

            $res = $api->getHistoricalScoreAndSkills($getToken);
            if (! $res->hasError()) {
                $gameData = $res->getData();
            }

            // Brain games
            $brainGames = CustomBlock::getBrainGamesData('programs/tasks', 'GET') ?? [];
            $brainGameTitles = $this->brainGameTitles($brainGames);

            $res2 = $api->getPlayedGames($getToken);
            if (! $res2->hasError()) {
                $d = $res2->getData();
                $playedGames = $d['historicalPlayedGames'] ?? [];
            }
        }

        $html = view('admin.users.user_details', compact(
            'info',
            'goals',
            'areas',
            'gameData',
            'playedGames',
            'brainGames',
            'brainGameTitles',
            'localSessions'
        ))->render();

        return response()->json([
            'status' => true,
            'html' => $html,
        ]);
    }

    public function registerUserInGame(Request $request, $id = null)
    {
        $request->validate([
            'locale' => 'required',
        ]);

        try {

            $userID = $id ?: $request->user_id;
            $locale = $request->locale;

            $user = User::findOrFail($userID);
            $userToken = $this->registerCognifitUser($user, $locale, $request->password);

            if (! filled($userToken)) {
                return redirect()->back()->with('error', 'CogniFit no devolvió token de usuario.');
            }

            return redirect()->back()->with('success', 'Elemento registrado correctamente.');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function updateGameLocale(Request $request, $id = null)
    {
        $request->validate([
            'locale' => 'required',
            'user_token' => 'required',
        ]);

        try {

            $locale = $request->locale;
            $userToken = $request->user_token;

            $cognifitApiUserAccount = new UserAccount(
                config('services.cognifit.client_id'),
                config('services.cognifit.client_secret')
            );
            $response = $cognifitApiUserAccount->update($userToken, new UserData([
                'user_locale' => $locale,
            ]));

            if (! $response->hasError()) {
                User::where('cognifit_user_token', $userToken)->update([
                    'cognifit_locale' => $locale,
                ]);

                return redirect()->back()->with('success', 'Idioma actualizado correctamente.');
            }

            return redirect()->back()->with('error', 'Failed to update user locale.');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function userReport(Request $request, $id)
    {
        $user = User::find($id);

        if (! $user) {
            abort(404, 'Elemento no encontrado en la base de datos');
        }

        $info = $this->userPayload($user);
        $goals = $this->normalizedUserInterest($info, 'goals');
        $areas = $this->normalizedUserInterest($info, 'areas');
        $playedGames = [];
        $brainGames = [];
        $brainGameTitles = [];
        $localSessions = CognitiveSession::query()
            ->where('user_id', $user->id)
            ->latest('completed_at')
            ->latest('id')
            ->limit(10)
            ->get();
        $getToken = $user->cognifit_user_token;

        // 2. Agar token hai to Cognifit API se games ka data lein
        if ($getToken && $getToken != '-') {
            $api = new UserActivity(
                config('services.cognifit.client_id'),
                config('services.cognifit.client_secret')
            );

            // Brain games ki list
            $brainGames = customBlock::getBrainGamesData('programs/tasks', 'GET') ?? [];
            $brainGameTitles = $this->brainGameTitles($brainGames);

            // Played games ka historical data
            $res2 = $api->getPlayedGames($getToken);
            if (! $res2->hasError()) {
                $d = $res2->getData();
                $playedGames = $d['historicalPlayedGames'] ?? [];
            }
        }

        // 3. Data array prepare karein view ke liye
        $data = [
            'info' => $info,
            'goals' => $goals,
            'areas' => $areas,
            'playedGames' => $playedGames,
            'brainGames' => $brainGames,
            'brainGameTitles' => $brainGameTitles,
            'localSessions' => $localSessions,
            'viewData' => customBlock::class,
            'issuedAt' => now(),
            'folio' => strtoupper(uniqid('GK-')),
        ];

        // // 4. Download PDF check
        // if ($request->has('download')) {
        //     $pdf = Pdf::loadView('admin.users.report_pdf', $data);
        //     return $pdf->download(($info['name'] ?? 'User') . '_Report.pdf');
        // }

        // 5. Web View show karein
        return view('admin.users.report_page', $data);
    }

    public function usersIndex()
    {
        return redirect()->route('admin.user.management');
    }

    public function createUser()
    {
        $title = 'Crear elemento';
        $catalogs = $this->operationalCatalogs();
        $ranks = $catalogs['ranks'];
        $units = $catalogs['units'];
        $groups = $catalogs['groups'];
        $areas = $catalogs['areas'];

        $data = compact('title', 'ranks', 'units', 'groups', 'areas');

        return view('admin.users.add')->with($data);
    }

    public function catalogs()
    {
        $title = 'Catálogos operativos';
        $catalogs = $this->operationalCatalogs();

        return view('admin.catalogs.index', compact('title') + $catalogs);
    }

    public function storeRank(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:120',
            'code' => 'nullable|string|max:60',
        ]);

        OperationalRank::firstOrCreate(
            ['name' => trim($data['name'])],
            ['code' => filled($data['code'] ?? null) ? trim($data['code']) : null, 'active' => true]
        );

        return redirect()->route('admin.catalogs.index')->with('success', 'Rango creado correctamente.');
    }

    public function storeUnit(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'code' => 'nullable|string|max:60',
            'type' => 'nullable|string|max:80',
        ]);

        SecurityUnit::firstOrCreate(
            ['name' => trim($data['name'])],
            [
                'code' => filled($data['code'] ?? null) ? trim($data['code']) : null,
                'type' => filled($data['type'] ?? null) ? trim($data['type']) : null,
                'active' => true,
            ]
        );

        return redirect()->route('admin.catalogs.index')->with('success', 'Unidad creada correctamente.');
    }

    public function storeGroup(Request $request)
    {
        $data = $request->validate([
            'security_unit_id' => 'nullable|exists:security_units,id',
            'name' => 'required|string|max:160',
            'code' => 'nullable|string|max:60',
            'shift' => 'nullable|string|max:80',
        ]);

        OperationalGroup::firstOrCreate(
            [
                'security_unit_id' => $data['security_unit_id'] ?? null,
                'name' => trim($data['name']),
            ],
            [
                'code' => filled($data['code'] ?? null) ? trim($data['code']) : null,
                'shift' => filled($data['shift'] ?? null) ? trim($data['shift']) : null,
                'active' => true,
            ]
        );

        return redirect()->route('admin.catalogs.index')->with('success', 'Grupo operativo creado correctamente.');
    }

    public function storeArea(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'code' => 'nullable|string|max:60',
        ]);

        AssignmentArea::firstOrCreate(
            ['name' => trim($data['name'])],
            ['code' => filled($data['code'] ?? null) ? trim($data['code']) : null, 'active' => true]
        );

        return redirect()->route('admin.catalogs.index')->with('success', 'Área creada correctamente.');
    }

    public function addUser()
    {

        $title = 'Agregar elemento';

        $data = compact('title');

        // return view("admin.users.add")->with($data);
    }

    public function usersStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'badge_number' => 'nullable|string|max:80',
            'rank_id' => 'nullable|exists:operational_ranks,id',
            'security_unit_id' => 'nullable|exists:security_units,id',
            'operational_group_id' => 'nullable|exists:operational_groups,id',
            'assignment_area_id' => 'nullable|exists:assignment_areas,id',
            'image' => 'nullable|image',
            'age' => 'nullable|integer|min:1|max:120',
            'gender' => 'nullable|in:male,female,other',
            'role' => 'required|in:user,admin,super_admin',
            'password' => 'required',
            'confirm_password' => 'nullable|same:password',
        ]);

        $imageName = null;

        if ($request->hasFile('image')) {
            FileHelper::createDirectory(public_path('UserImages'));
            $imageName = FileHelper::uploadImage($request->file('image'), 'UserImages');
        }

        $rank = OperationalRank::find($request->rank_id);
        $unit = SecurityUnit::find($request->security_unit_id);
        $group = OperationalGroup::find($request->operational_group_id);
        $area = AssignmentArea::find($request->assignment_area_id);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'badge_number' => $request->badge_number,
            'rank' => $rank?->name,
            'assignment_area' => $area?->name,
            'security_unit_id' => $unit?->id,
            'operational_group_id' => $group?->id,
            'image' => $imageName,
            'age' => $request->filled('age') ? $request->age : null,
            'gender' => $request->filled('gender') ? $request->gender : null,
            'password' => $request->password,
            'status' => 1,
            'role' => $request->role,
        ]);

        if ($user->role !== 'user') {
            return redirect()->route('admin.user.management')->with('success', 'Perfil administrativo creado correctamente.');
        }

        try {
            $userToken = $this->registerCognifitUser($user, 'es', $request->password);

            if (! filled($userToken)) {
                return redirect()
                    ->route('admin.user.management')
                    ->with('warning', 'Elemento creado, pero CogniFit no devolvió token de usuario.');
            }
        } catch (Throwable $th) {
            return redirect()
                ->route('admin.user.management')
                ->with('warning', 'Elemento creado, pero el registro en CogniFit falló: '.$th->getMessage());
        }

        return redirect()->route('admin.user.management')->with('success', 'Elemento creado y registrado en CogniFit correctamente.');
    }

    public function usersEdit($id)
    {
        return redirect()->route('admin.user.management');
    }

    public function usersUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'badge_number' => 'nullable|string|max:80',
            'rank_id' => 'nullable|exists:operational_ranks,id',
            'security_unit_id' => 'nullable|exists:security_units,id',
            'operational_group_id' => 'nullable|exists:operational_groups,id',
            'assignment_area_id' => 'nullable|exists:assignment_areas,id',
            'image' => 'nullable|image',
            'age' => 'nullable|integer|min:1|max:120',
            'gender' => 'nullable|in:male,female,other',
            'role' => 'required|in:user,admin,super_admin',
            'status' => 'required|in:0,1,2',
            'password' => 'nullable|string|min:8',
            'confirm_password' => 'nullable|same:password',
        ]);

        $rank = OperationalRank::find($request->rank_id);
        $unit = SecurityUnit::find($request->security_unit_id);
        $group = OperationalGroup::find($request->operational_group_id);
        $area = AssignmentArea::find($request->assignment_area_id);

        $payload = [
            'name' => $request->name,
            'email' => $request->email,
            'badge_number' => $request->badge_number,
            'rank' => $rank?->name,
            'assignment_area' => $area?->name,
            'security_unit_id' => $unit?->id,
            'operational_group_id' => $group?->id,
            'age' => $request->filled('age') ? $request->age : null,
            'gender' => $request->filled('gender') ? $request->gender : null,
            'status' => (int) $request->status,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $payload['password'] = $request->password;
        }

        if ($request->hasFile('image')) {
            FileHelper::createDirectory(public_path('UserImages'));
            $payload['image'] = FileHelper::uploadImage($request->file('image'), 'UserImages');
        }

        $user->update($payload);

        if ($this->database()) {
            $this->database()->getReference('users/'.$user->id)->update([
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image,
                'age' => $user->age,
                'gender' => $user->gender,
                'status' => $user->status,
            ]);
        }

        return redirect()->route('admin.user.management')->with('success', 'Elemento actualizado correctamente.');
    }

    // Delete User
    public function usersDestroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return redirect()->route('admin.user.management')->with('success', 'Elemento eliminado correctamente');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Ocurrió un error');
        }
    }

    public function listGames()
    {
        $title = 'Simuladores CogniFit';
        $list = customBlock::getBrainGamesData('programs/tasks', 'GET')
            ->filter(fn ($game) => filled($game->key ?? null))
            ->sortBy(fn ($game) => $game->assets->titles->es ?? $game->assets->titles->en ?? $game->key)
            ->values();

        $data = compact('title', 'list');

        return view('admin.games.index')->with($data);
    }

    public function downloadExcel()
    {

        $dataNow = Carbon::now()->toDateTimeString();

        $name = 'import_users_'.$dataNow.'.xlsx';

        return Excel::download(new userExport, $name);
    }

    public function getExcelData(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        session()->forget('excel_data');

        $import = new UserImport;
        Excel::import($import, $request->file('file'));

        $importedData = $import->getData();

        session()->put('excel_data', $importedData);

        return redirect()->route('admin.review.excel')->with('success', 'Revisa los datos antes de guardar.');
    }

    public function reviewExcelData()
    {
        $title = 'Review Excel Sheet';

        if (! session()->has('excel_data')) {
            return redirect()->route('admin.user.management')->with('error', 'No se encontraron datos.');
        }

        $list = session()->get('excel_data');
        $catalogs = $this->operationalCatalogs();
        $ranks = $catalogs['ranks'];
        $units = $catalogs['units'];
        $groups = $catalogs['groups'];
        $areas = $catalogs['areas'];

        $data = compact('title', 'list', 'ranks', 'units', 'groups', 'areas');

        return view('admin.excel.import_preview')->with($data);
    }

    public function storeExcelData(Request $request)
    {

        $request->validate([
            'rows' => 'required',
        ]);

        // try {
        set_time_limit(3600);

        $rows = $request->rows;

        $count = 0;
        $skipped = 0;
        $cognifitCount = 0;
        $cognifitErrors = [];

        foreach ($rows as $row) {

            $name = trim((string) ($row['name'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $age = trim((string) ($row['age'] ?? ''));
            $gender = trim((string) ($row['gender'] ?? ''));
            $password = trim((string) ($row['password'] ?? ''));
            $badgeNumber = trim((string) ($row['badge_number'] ?? '')) ?: null;
            $rank = $this->resolveRankNameById($row['rank_id'] ?? null)
                ?? $this->resolveRankName($row['rank'] ?? null);
            $unit = $this->resolveUnitById($row['security_unit_id'] ?? null);
            $group = $this->resolveGroupById($row['operational_group_id'] ?? null);
            $assignmentArea = $this->resolveAreaNameById($row['assignment_area_id'] ?? null)
                ?? $this->resolveAreaName($row['assignment_area'] ?? null);

            if (! $unit || ! $group) {
                [$resolvedUnit, $resolvedGroup] = $this->resolveOperationalAssignment(
                    $row['security_unit'] ?? null,
                    $row['operational_group'] ?? null
                );

                $unit ??= $resolvedUnit;
                $group ??= $resolvedGroup;
            }

            if (
                $name === '' ||
                ! filter_var($email, FILTER_VALIDATE_EMAIL) ||
                $password === ''
            ) {
                $skipped++;

                continue;
            }

            if (! User::where('email', $email)->exists()) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'badge_number' => $badgeNumber,
                    'rank' => $rank,
                    'assignment_area' => $assignmentArea,
                    'security_unit_id' => $unit?->id,
                    'operational_group_id' => $group?->id,
                    'image' => null,
                    'age' => $age !== '' ? $age : null,
                    'gender' => $gender !== '' ? strtolower($gender) : null,
                    'password' => $password,
                    'status' => 1,
                    'role' => 'user',
                ]);

                try {
                    $token = $this->registerCognifitUser($user, 'es', $password);

                    if (filled($token)) {
                        $cognifitCount++;
                    }
                } catch (Throwable $th) {
                    $cognifitErrors[] = $email.': '.$th->getMessage();
                }

                $count++;
            } else {
                $skipped++;
            }

        }

        $message = $count.' elementos importados correctamente. '.$cognifitCount.' registrados en CogniFit.';

        if ($skipped > 0) {
            $message .= ' '.$skipped.' filas omitidas por nombre/correo/contraseña incompletos, correo inválido o correo duplicado.';
        }

        if ($cognifitErrors !== []) {
            return redirect()
                ->route('admin.user.management')
                ->with('warning', $message.' Fallas CogniFit: '.implode(' | ', array_slice($cognifitErrors, 0, 5)));
        }

        return redirect()->route('admin.user.management')->with('success', $message);

        // } catch (\Throwable $th) {
        //     return redirect()->back()->with("error", "Something went wrong");
        // }
    }

    private function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'image' => $user->image,
            'age' => $user->age,
            'gender' => $user->gender,
            'badge_number' => $user->badge_number,
            'rank' => $user->rank,
            'rank_id' => OperationalRank::query()->where('name', $user->rank)->value('id'),
            'unit' => $user->securityUnit?->name,
            'security_unit_id' => $user->security_unit_id,
            'operational_group' => $user->operationalGroup?->name,
            'operational_group_id' => $user->operational_group_id,
            'assignment_area' => $user->assignment_area,
            'assignment_area_id' => AssignmentArea::query()->where('name', $user->assignment_area)->value('id'),
            'status' => $user->status,
            'role' => $user->role ?? 'user',
            'user_token' => $user->cognifit_user_token,
            'cognifit_user_token' => $user->cognifit_user_token,
            'cognifit_locale' => $user->cognifit_locale,
            'cognifit_registered_at' => optional($user->cognifit_registered_at)->toDateTimeString(),
            'created_at' => optional($user->created_at)->toDateTimeString(),
            'userIntrest' => [
                'goals' => [],
                'areas' => [],
            ],
        ];
    }

    private function normalizedUserInterest(array $info, string $key): array
    {
        $items = $info['userIntrest'][$key] ?? [];

        if (! is_array($items)) {
            return [];
        }

        return array_values($items);
    }

    private function brainGameTitles(mixed $brainGames): array
    {
        $titles = [];

        foreach ($brainGames ?? [] as $game) {
            $key = is_array($game) ? ($game['key'] ?? null) : ($game->key ?? null);

            if (! $key) {
                continue;
            }

            $spanishTitle = is_array($game)
                ? data_get($game, 'assets.titles.es')
                : ($game->assets->titles->es ?? null);
            $englishTitle = is_array($game)
                ? data_get($game, 'assets.titles.en')
                : ($game->assets->titles->en ?? null);

            $titles[$key] = $spanishTitle ?? $englishTitle ?? $key;
        }

        return $titles;
    }

    private function registerCognifitUser(User $user, string $locale = 'es', ?string $password = null): ?string
    {
        if (filled($user->cognifit_user_token)) {
            return $user->cognifit_user_token;
        }

        if (! filled(config('services.cognifit.client_id')) || ! filled(config('services.cognifit.client_secret'))) {
            throw new \RuntimeException('Faltan COGNIFIT_CLIENT_ID o COGNIFIT_CLIENT_SECRET en .env.');
        }

        $cognifitApiUserAccount = new UserAccount(
            config('services.cognifit.client_id'),
            config('services.cognifit.client_secret')
        );

        $response = $cognifitApiUserAccount->registration(new UserData([
            'user_name' => $user->name,
            'user_email' => $user->email,
            'user_birthday' => Carbon::now()->subYears($user->age ?: 18)->startOfYear()->format('Y-m-d'),
            'user_locale' => $locale,
            'user_password' => $this->cognifitPassword(),
        ]));

        if ($response->hasError()) {
            throw new \RuntimeException('CogniFit rechazó el registro del usuario: '.$this->cognifitErrorDetail($response));
        }


        $userToken = $response->get('user_token');

        if (filled($userToken)) {
            $user->forceFill([
                'cognifit_user_token' => $userToken,
                'cognifit_locale' => $locale,
                'cognifit_registered_at' => now(),
            ])->save();
        }

        return $userToken;
    }

    private function cognifitErrorDetail(mixed $response): string
    {
        foreach (['getErrorMessage', 'getError', 'getErrors', 'getMessage'] as $method) {
            if (! method_exists($response, $method)) {
                continue;
            }

            try {
                $detail = $this->stringifyCognifitValue($response->{$method}());

                if ($detail !== '') {
                    return $detail;
                }
            } catch (\Throwable $th) {
                //
            }
        }

        foreach (['getData', 'getResponse'] as $method) {
            if (! method_exists($response, $method)) {
                continue;
            }

            try {
                $detail = $this->stringifyCognifitValue($response->{$method}());

                if ($detail !== '') {
                    return $detail;
                }
            } catch (\Throwable $th) {
                //
            }
        }

        return 'sin detalle devuelto por el SDK.';
    }

    private function cognifitPassword(): string
    {
        return 'Gk'.StringHelper::randomString(10).'9@';
    }

    private function stringifyCognifitValue(mixed $value): string
    {
        if (is_string($value)) {
            return trim($value);
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        if (is_array($value) || is_object($value)) {
            return trim((string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        return '';
    }

    private function resolveOperationalAssignment(?string $unitName, ?string $groupName): array
    {
        $unitName = trim((string) $unitName);
        $groupName = trim((string) $groupName);

        $unit = $unitName !== ''
            ? SecurityUnit::firstOrCreate(['name' => $unitName], ['active' => true])
            : null;

        $group = $groupName !== ''
            ? OperationalGroup::firstOrCreate(
                [
                    'security_unit_id' => $unit?->id,
                    'name' => $groupName,
                ],
                ['active' => true]
            )
            : null;

        return [$unit, $group];
    }

    private function resolveRankNameById(mixed $rankId): ?string
    {
        if (! filled($rankId)) {
            return null;
        }

        return OperationalRank::query()->find($rankId)?->name;
    }

    private function resolveUnitById(mixed $unitId): ?SecurityUnit
    {
        if (! filled($unitId)) {
            return null;
        }

        return SecurityUnit::query()->find($unitId);
    }

    private function resolveGroupById(mixed $groupId): ?OperationalGroup
    {
        if (! filled($groupId)) {
            return null;
        }

        return OperationalGroup::query()->find($groupId);
    }

    private function resolveAreaNameById(mixed $areaId): ?string
    {
        if (! filled($areaId)) {
            return null;
        }

        return AssignmentArea::query()->find($areaId)?->name;
    }

    private function resolveRankName(?string $rankName): ?string
    {
        $rankName = trim((string) $rankName);

        if ($rankName === '') {
            return null;
        }

        return OperationalRank::firstOrCreate(['name' => $rankName], ['active' => true])->name;
    }

    private function resolveAreaName(?string $areaName): ?string
    {
        $areaName = trim((string) $areaName);

        if ($areaName === '') {
            return null;
        }

        return AssignmentArea::firstOrCreate(['name' => $areaName], ['active' => true])->name;
    }

    private function operationalCatalogs(): array
    {
        return [
            'ranks' => OperationalRank::query()->where('active', true)->orderBy('name')->get(),
            'units' => SecurityUnit::query()->where('active', true)->orderBy('name')->get(),
            'groups' => OperationalGroup::query()->with('unit')->where('active', true)->orderBy('name')->get(),
            'areas' => AssignmentArea::query()->where('active', true)->orderBy('name')->get(),
        ];
    }

    private function database()
    {
        if ($this->database !== null) {
            return $this->database;
        }

        try {
            $this->database = app('firebase.database');
        } catch (Throwable $th) {
            $this->database = false;
        }

        return $this->database ?: null;
    }
}

