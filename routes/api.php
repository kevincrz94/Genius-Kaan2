<?php

use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\CognifitApiController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\Api\SessionApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);
Route::get('/cognifit/status', [CognifitApiController::class, 'status']);

Route::post('/auth/login', [AuthApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthApiController::class, 'me']);
    Route::post('/auth/logout', [AuthApiController::class, 'logout']);

    Route::get('/users', [UserApiController::class, 'index']);
    Route::get('/users/{user}', [UserApiController::class, 'show']);

    Route::get('/sessions', [SessionApiController::class, 'index']);
    Route::post('/sessions', [SessionApiController::class, 'store']);
    Route::get('/sessions/{session}', [SessionApiController::class, 'show']);
    Route::put('/sessions/{session}', [SessionApiController::class, 'update']);

    Route::get('/reports/users/{user}', [ReportApiController::class, 'userReport']);

    Route::prefix('cognifit')->group(function () {
        Route::post('/users/{user}/register', [CognifitApiController::class, 'registerUser']);
        Route::put('/users/{user}/locale', [CognifitApiController::class, 'updateLocale']);
        Route::post('/users/{user}/launch', [CognifitApiController::class, 'launch']);
        Route::get('/users/{user}/scores', [CognifitApiController::class, 'scores']);
        Route::get('/users/{user}/played-games', [CognifitApiController::class, 'playedGames']);
    });
});

// API For Launching Game
Route::post('/launch-game', [ApiController::class, 'getLaunchGame']);

// API For Getting All Users
Route::get('/get-all-users', [ApiController::class, 'getAllUsers']);

// API For Uploading Files to the server
Route::post('/store-files', [ApiController::class, 'storeFiles']);
