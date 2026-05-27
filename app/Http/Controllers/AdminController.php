<?php

namespace App\Http\Controllers;

use App\Exports\userExport;
use App\Imports\UserImport;
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

        $title = 'Login';

        $data = compact('title');

        return view('admin.login')->with($data);

    }

    public function loginCheck(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:9',
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
                return redirect()->back()->with('error', 'Invalid Email!');
            }

            if ($passwordCheck != $adminPassword) {
                return redirect()->back()->with('error', 'Invalid Password!');
            }

            $generateToken = StringHelper::randomString(20);

            /***** This method is working for the generation of the logs *****/
            $logArray = [
                'log' => $generateToken,
                'type' => 'Login',
                'created_at' => date('Y-m-d H:i:s'),
            ];

            if ($this->database()) {
                customBlock::generateLogs('logs', $logArray, $this->database());
            }
            /***** End Method *****/

            session()->put('admin_id', $generateToken);

            return redirect()->route('admin.dashboard')->with('success', 'Logged in successfully.');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something Went Wrong!');
        }
    }

    public function logout()
    {
        session()->forget('admin_id');

        return redirect()->route('admin.showLogin')->with('success', 'Logged out successfully.');
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
        $title = 'User Management';

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
            return response()->json(['status' => false, 'message' => 'User not found']);
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
                return redirect()->back()->with('error', 'Cognifit did not return a user token.');
            }

            return redirect()->back()->with('success', 'User registered successfully.');

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

                return redirect()->back()->with('success', 'User locale updated successfully.');
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
            abort(404, 'User not found in database');
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
        $title = 'Create User';

        $data = compact('title');

        return view('admin.users.add')->with($data);
    }

    public function addUser()
    {

        $title = 'Add User';

        $data = compact('title');

        // return view("admin.users.add")->with($data);
    }

    public function usersStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
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

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
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
                    ->with('warning', 'User created, but Cognifit did not return a user token.');
            }
        } catch (Throwable $th) {
            return redirect()
                ->route('admin.user.management')
                ->with('warning', 'User created, but Cognifit registration failed: '.$th->getMessage());
        }

        return redirect()->route('admin.user.management')->with('success', 'User created successfully');
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

        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }

    // Delete User
    public function usersDestroy($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            return redirect()->route('admin.user.management')->with('success', 'User deleted successfully');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong');
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

        return redirect()->route('admin.review.excel')->with('success', 'Please review the data before saving.');
    }

    public function reviewExcelData()
    {
        $title = 'Review Excel Sheet';

        if (! session()->has('excel_data')) {
            return redirect()->route('admin.user.management')->with('error', 'No data found !');
        }

        $list = session()->get('excel_data');

        $data = compact('title', 'list');

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

            if (! User::where('email', $email)->exists()) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
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

        return redirect()->route('admin.user.management')->with('success', $count.' Users imported successfully');

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
            return null;
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
