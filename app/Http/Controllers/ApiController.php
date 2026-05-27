<?php

namespace App\Http\Controllers;

use App\Services\customBlock;
use App\Services\FileHelper;
use App\Services\StringHelper;
use CognifitSdk\Api\UserAccount;
use CognifitSdk\Lib\UserData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
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

    public function getLaunchGame(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_token' => 'required',
            'game_key' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ]);
        }

        $userToken = $request->user_token;
        $gameKeyLower = strtolower($request->game_key);
        $gameKey = strtoupper($gameKeyLower);

        $launchingURL = route('start.game', [
            'user_token' => $userToken,
            'game_key' => $gameKey,
        ]);

        $data = compact('gameKey', 'userToken', 'launchingURL');

        return response($data, 200)->header('Content-type', 'Application/json');
    }

    public function getAllUsers()
    {
        $getAllUsers = customBlock::getFireBaseData('users', $this->database);

        $tokens = [];

        foreach ($getAllUsers as $userId => $userData) {
            if (! empty($userData['user_token'])) {
                $tokens[] = $userData['user_token'];
                $cognifitApiUserAccount = new UserAccount(
                    config('services.cognifit.client_id'),
                    config('services.cognifit.client_secret')
                );
                $response = $cognifitApiUserAccount->update($userData['user_token'], new UserData([
                    'user_locale' => 'es',
                ]));
            }
        }

        dd($tokens);
    }

    public function storeFiles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file.*' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ]);
        }

        $files = StringHelper::isJson($request->file) ? json_decode($request->file, true) : $request->file;

        $fileNameArray = [];

        foreach ($files as $key => $file) {
            $fileName = FileHelper::uploadImage($file, 'uploadedFiles');
            $fileNameArray[] = $fileName;
        }

        return response()->json([
            'status' => 200,
            'message' => 'Files uploaded successfully',
            'data' => $fileNameArray,
        ]);
    }
}
