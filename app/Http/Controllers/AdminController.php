<?php

namespace App\Http\Controllers;

use App\Exports\userExport;
use App\Imports\UserImport;
use App\Models\AssignmentArea;
use App\Models\OperationalGroup;
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
        if (session('admin_id')) {
            return redirect()->back();
        }

        $title = 'Ingreso';

        $data = compact('title');

        return view('admin.login')->with($data);

    }

    public function loginCheck(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        try {
            $emailCheck = $request->email;
            $passwordCheck = $request->password;

            $adminEmail = null;
            $adminPassword = null;

            $getAdminData = $this->database()
                ? customBlock::getFireBaseData('admin', $this->database())
                : collect([]);

            if ($getAdminData && isset($getAdminData['email'], $getAdminData['password'])) {
                $adminEmail = $getAdminData['email'];
                $adminPassword = $getAdminData['password'];
            }

            $adminEmail = $adminEmail ?: config('admin.email');
            $adminPassword = $adminPassword ?: config('admin.password');

            if ($adminEmail != $emailCheck) {
                return redirect()->back()->with('error', 'Este acceso es solo para administrador. Usa el correo configurado en ADMIN_EMAIL.');
            }

            if ($passwordCheck != $adminPassword) {
                return redirect()->back()->with('error', 'Contraseña no válida.');
            }

            $generateToken = StringHelper::randomString(20);

            /***** This method is working for the generation of the logs *****/
            $logArray = [
                'log' => $generateToken,
                'type' => 'Ingreso',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->database()) {
                customBlock::generateLogs('logs', $logArray, $this->database());
            }
            /***** End Method *****/

            session()->put('admin_id', $generateToken);

            return redirect()->route('admin.dashboard')->with('success', 'Sesión iniciada correctamente.');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Ocurrió un error.');
        }
    }

    public function logout()
    {
        session()->forget('admin_id');

        return redirect()->route('admin.showLogin')->with('success', 'Sesión cerrada correctamente.');
    }

    public function dashboard()
    {
        return redirect()->route('admin.user.management');
    }

    public function skillManagement()
    {
        $title = 'Skill Management';

        $endPoint = 'skills';
        $method = 'GET';

        $list = customBlock::getSDKData($endPoint, $method);

        $data = compact('title', 'list');

        return view('admin.categories.index')->with($data);
    }

    public function userManagement()
    {
        $title = 'Gestion de elementos';

        $list = User::query()
            ->latest('id')
            ->get()
            ->map(fn (User $user) => $this->userPayload($user));

        $data = compact('title', 'list');

        return view('admin.users.index')->with($data);
    }

    public function userProfile($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['status' => false, 'message' => 'Elemento no encontrado']);
        }

        $info = $this->userPayload($user);
        $gameData = null;
        $playedGames = [];
        $brainGames = [];

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

            $res2 = $api->getPlayedGames($getToken);
            if (! $res2->hasError()) {
                $d = $res2->getData();
                $playedGames = $d['historicalPlayedGames'] ?? [];
            }
        }

        $html = view('admin.users.user_details', compact('info', 'gameData', 'playedGames', 'brainGames'))->render();

        return response()->json([
            'status' => true,
            'html' => $html,
        ]);
    }

    public function registerUserInGame(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'locale' => 'required',
        ]);

        try {

            $userID = $request->user_id;
            $locale = $request->locale;

            $user = User::findOrFail($userID);
            $userToken = $this->registerCognifitUser($user, $locale, $request->password);

            if (! filled($userToken)) {
                return redirect()->back()->with('error', 'Cognifit no devolvió token de usuario.');
            }

            return redirect()->back()->with('success', 'Elemento registrado correctamente.');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function updateGameLocale(Request $request)
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
        $playedGames = [];
        $brainGames = [];
        $getToken = $user->cognifit_user_token;

        // 2. Agar token hai to Cognifit API se games ka data lein
        if ($getToken && $getToken != '-') {
            $api = new UserActivity(
                config('services.cognifit.client_id'),
                config('services.cognifit.client_secret')
            );

            // Brain games ki list
            $brainGames = customBlock::getBrainGamesData('programs/tasks', 'GET') ?? [];

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
            'playedGames' => $playedGames,
            'brainGames' => $brainGames,
            'viewData' => customBlock::class,
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
        $users = User::all();

        return view('admin.users.showall', compact('users'));
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
            'age' => 'required',
            'gender' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
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
            'age' => $request->age,
            'gender' => $request->gender,
            'password' => $request->password,
            'status' => 1,
        ]);

        try {
            $userToken = $this->registerCognifitUser($user, 'es', $request->password);

            if (! filled($userToken)) {
                return redirect()
                    ->route('admin.user.management')
                    ->with('warning', 'Elemento creado, pero Cognifit no devolvió token de usuario.');
            }
        } catch (Throwable $th) {
            return redirect()
                ->route('admin.user.management')
                ->with('warning', 'Elemento creado, pero el registro en Cognifit falló: '.$th->getMessage());
        }

        return redirect()->route('admin.user.management')->with('success', 'Elemento creado correctamente');
    }

    public function usersEdit($id)
    {
        $user = User::findOrFail($id);
        $users = User::all();

        return view('admin.users.showall', compact('users', 'user'));
    }

    public function usersUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // ... validation aur image handling ...

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'age' => $request->age,
            'gender' => $request->gender,
            // password agar hai to
        ]);

        if ($request->hasFile('image')) {
            // old image delete + new upload
            $imageName = time().'_'.$request->image->getClientOriginalName();
            $request->image->move(public_path('profiles'), $imageName);
            $user->image = $imageName;
            $user->save();
        }

        // Firebase mein UID ke naam se update karo
        $firebaseUid = $user->id; // ya jo bhi tum use kar rahe ho

        if ($this->database()) {
            $this->database()->getReference('users/'.$firebaseUid)->update([
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image ?? $user->getOriginal('image'),
                'age' => $user->age,
                'gender' => $user->gender,
                'status' => $user->status,
            ]);
        }

        return redirect()->route('admin.users')->with('success', 'Elemento actualizado correctamente');
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
        $title = 'List Games';

        $endPoint = 'programs/tasks';
        $method = 'GET';

        $list = customBlock::getSDKData($endPoint, $method, $locales = 'en');

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

        return view('admin.excel.view')->with($data);
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

        foreach ($rows as $row) {

            $name = $row['name'];
            $email = $row['email'];
            $age = $row['age'];
            $gender = $row['gender'];
            $password = $row['password'];
            $badgeNumber = $row['badge_number'] ?? null;
            $rank = $this->resolveRankName($row['rank'] ?? null);
            $unitName = $row['security_unit'] ?? null;
            $groupName = $row['operational_group'] ?? null;
            $assignmentArea = $this->resolveAreaName($row['assignment_area'] ?? null);

            if (! User::where('email', $email)->exists()) {
                [$unit, $group] = $this->resolveOperationalAssignment($unitName, $groupName);

                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'badge_number' => $badgeNumber,
                    'rank' => $rank,
                    'assignment_area' => $assignmentArea,
                    'security_unit_id' => $unit?->id,
                    'operational_group_id' => $group?->id,
                    'image' => null,
                    'age' => $age,
                    'gender' => strtolower($gender),
                    'password' => $password,
                    'status' => 1,
                ]);

                try {
                    $this->registerCognifitUser($user, 'es', $password);
                } catch (Throwable $th) {
                    //
                }

                $count++;
            }

        }

        return redirect()->route('admin.user.management')->with('success', $count.' elementos importados correctamente');

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
            'unit' => $user->securityUnit?->name,
            'operational_group' => $user->operationalGroup?->name,
            'assignment_area' => $user->assignment_area,
            'status' => $user->status,
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
            'user_password' => 'Web@'.($password ?: StringHelper::randomString(12)),
        ]));

        if ($response->hasError()) {
            throw new \RuntimeException('Cognifit rechazó el registro del usuario: '.$this->cognifitErrorDetail($response));
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

