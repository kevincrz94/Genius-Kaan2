<?php

namespace App\Http\Controllers;

use App\Exports\userExport;
use App\Imports\UserImport;
use App\Models\User;
use App\Services\customBlock;
use App\Services\FileHelper;
use App\Services\StringHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use CognifitSdk\Api\UserAccount;
use CognifitSdk\Api\UserActivity;
use CognifitSdk\Lib\UserData;
use Illuminate\Http\Request;
// Top par
use Maatwebsite\Excel\Facades\Excel;

class AdminController extends Controller
{
    protected $database;

    protected $auth;

    /**
     * Initialize Firebase Realtime Database and Authentication.
     */
    public function __construct()
    {
        $this->database = app('firebase.database');

        $this->auth = app('firebase.auth');
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

            $getAdminData = customBlock::getFireBaseData('admin', $this->database);

            if ($getAdminData) {
                $adminEmail = $getAdminData['email'];
                $adminPassword = $getAdminData['password'];
            }

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

            customBlock::generateLogs('logs', $logArray, $this->database);
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

        $list = customBlock::getFireBaseData('users', $this->database);

        $data = compact('title', 'list');

        return view('admin.users.index')->with($data);
    }

    public function userProfile($id)
    {
        $viewData = customBlock::class;
        $info = customBlock::getSpecificData('users/'.$id, $this->database);

        if (! $info) {
            return response()->json(['status' => false, 'message' => 'User not found']);
        }

        $gameData = null;
        $playedGames = [];
        $brainGames = [];

        $getToken = $info['user_token'] ?? null;
        if ($getToken && $getToken != '-') {
            $api = new \CognifitSdk\Api\UserActivity(env('COGNIFIT_API_KEY'), env('COGNIFIT_SECRET_KEY'));

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
            'user_id' => 'required',
            'locale' => 'required',
        ]);

        try {

            $userID = $request->user_id;
            $locale = $request->locale;

            $getSpecificUser = customBlock::getSpecificData('users/'.$userID, $this->database);

            $name = $getSpecificUser['name'];
            $email = $getSpecificUser['email'];
            $userBirth = Carbon::parse('16-Oct-1999')->format('Y-m-d');
            $userPassword = 'Web@'.$request->password;

            $cognifitApiUserAccount = new UserAccount(
                env('COGNIFIT_API_KEY'),
                env('COGNIFIT_SECRET_KEY')
            );

            $response = $cognifitApiUserAccount->registration(new UserData([
                'user_name' => $name,
                'user_email' => $email,
                'user_birthday' => $userBirth,
                'user_locale' => $locale,
                'user_password' => $userPassword,
            ]));

            $userToken = null;

            if (! $response->hasError()) {
                $cognifitUserToken = $response->get('user_token');
                if ($cognifitUserToken) {
                    $userToken = $cognifitUserToken;
                }
            }

            $this->database->getReference('users/'.$userID)->update([
                'user_token' => $userToken,
            ]);

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
                env('COGNIFIT_API_KEY'),
                env('COGNIFIT_SECRET_KEY')
            );
            $response = $cognifitApiUserAccount->update($userToken, new UserData([
                'user_locale' => $locale,
            ]));

            if (! $response->hasError()) {
                return redirect()->back()->with('success', 'User locale updated successfully.');
            }

            return redirect()->back()->with('error', 'Failed to update user locale.');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }
    }

    public function userReport(Request $request, $id)
    {
        // 1. Data fetch karein usi tarah jaise profile mein kiya tha
        $info = customBlock::getSpecificData('users/'.$id, $this->database);

        if (! $info) {
            abort(404, 'User not found in database');
        }

        $playedGames = [];
        $brainGames = [];
        $getToken = $info['user_token'] ?? null;

        // 2. Agar token hai to Cognifit API se games ka data lein
        if ($getToken && $getToken != '-') {
            $api = new UserActivity(env('COGNIFIT_API_KEY'), env('COGNIFIT_SECRET_KEY'));

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
            'email' => 'required|email',
            'image' => 'required|image',
            'age' => 'required',
            'gender' => 'required',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);

        $imageName = FileHelper::uploadImage($request->file('image'), 'UserImages');

        // Checking The Email Existing
        $userBool = customBlock::getUserBool($request->email, $this->auth);

        if ($userBool == true) {
            return redirect()->back()->with('error', 'Email Already Exists!');
        }

        // Pehle SQL mein user create karo
        $user = customBlock::createUser($request->name, $request->email, $request->password, $this->auth);

        $firebaseUid = $user;

        $userName = $request->name;
        $userEmail = $request->email;
        $userBirth = Carbon::parse('16-Oct-1999')->format('Y-m-d');
        $locale = 'es';
        $userPassword = 'Web@'.$request->password;

        $cognifitApiUserAccount = new UserAccount(
            env('COGNIFIT_API_KEY'),
            env('COGNIFIT_SECRET_KEY')
        );

        $response = $cognifitApiUserAccount->registration(new UserData([
            'user_name' => $userName,
            'user_email' => $userEmail,
            'user_birthday' => $userBirth,
            'user_locale' => $locale,
            'user_password' => $userPassword,
        ]));

        $userToken = null;

        if (! $response->hasError()) {
            $cognifitUserToken = $response->get('user_token');
            if ($cognifitUserToken) {
                $userToken = $cognifitUserToken;
            }
        }

        $this->database->getReference('users/'.$firebaseUid)->set([
            'id' => $firebaseUid,
            'name' => $request->name,
            'email' => $request->email,
            'image' => $imageName,
            'age' => $request->age,
            'gender' => $request->gender,
            'status' => 1,
            'user_token' => $userToken,
            'created_at' => now()->toDateTimeString(),
        ]);

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

        $this->database->getReference('users/'.$firebaseUid)->update([
            'name' => $user->name,
            'email' => $user->email,
            'image' => $user->image ?? $user->getOriginal('image'),
            'age' => $user->age,
            'gender' => $user->gender,
            'status' => $user->status,
        ]);

        return redirect()->route('admin.users')->with('success', 'User updated successfully');
    }

    // Delete User
    public function usersDestroy($id)
    {
        try {

            $boolUser = customBlock::getUserBool($id, $this->auth);

            if ($boolUser) {
                customBlock::deleteCreatedUser($id, $this->auth);

                customBlock::deleteRecord('users/'.$id, $this->database);

                return redirect()->route('admin.user.management')->with('success', 'User deleted successfully');
            }

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

            $checkUser = customBlock::getUserBool($email, $this->auth);

            if ($checkUser == false) {

                $UID = customBlock::createUser($name, $email, $password, $this->auth);

                $firebaseUid = $UID;

                $userName = $name;
                $userEmail = $email;
                $userBirth = Carbon::parse('16-Oct-1999')->format('Y-m-d');
                $locale = 'es';
                $userPassword = 'Web@'.$password;

                $cognifitApiUserAccount = new UserAccount(
                    env('COGNIFIT_API_KEY'),
                    env('COGNIFIT_SECRET_KEY')
                );

                $response = $cognifitApiUserAccount->registration(new UserData([
                    'user_name' => $userName,
                    'user_email' => $userEmail,
                    'user_birthday' => $userBirth,
                    'user_locale' => $locale,
                    'user_password' => $userPassword,
                ]));

                $userToken = null;

                if (! $response->hasError()) {
                    $cognifitUserToken = $response->get('user_token');
                    if ($cognifitUserToken) {
                        $userToken = $cognifitUserToken;
                    }
                }

                $this->database->getReference('users/'.$firebaseUid)->update([
                    'name' => $name,
                    'email' => $email,
                    'image' => 'default.png',
                    'age' => $age,
                    'gender' => $gender,
                    'status' => 1,
                    'id' => $firebaseUid,
                    'userToken' => $userToken,
                    'created_at' => now()->toDateTimeString(),
                ]);

                $count++;
            }

        }

        return redirect()->route('admin.user.management')->with('success', $count.' Users imported successfully');

        // } catch (\Throwable $th) {
        //     return redirect()->back()->with("error", "Something went wrong");
        // }
    }
}
