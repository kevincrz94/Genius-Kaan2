<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminMetricsController;
use App\Http\Middleware\Admin;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {

    // Route For Login
    Route::get('/login', [AdminController::class, 'login'])->name('admin.showLogin');
    Route::post('/login', [AdminController::class, 'loginCheck'])->name('admin.login');
    Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');

    Route::middleware(Admin::class)->group(function () {
        // Route For Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // Route For Skills
        Route::get('/skills', [AdminController::class, 'skillManagement'])->name('admin.skills.management');

        Route::prefix('metrics')->group(function () {
            Route::get('/', [AdminMetricsController::class, 'index'])->name('admin.metrics.index');
            Route::get('/users/{user}', [AdminMetricsController::class, 'user'])->name('admin.metrics.user');
            Route::post('/users/{user}/metrics', [AdminMetricsController::class, 'storeUserMetric'])->name('admin.metrics.user.store');
        });

        Route::prefix('catalogs')->group(function () {
            Route::get('/', [AdminController::class, 'catalogs'])->name('admin.catalogs.index');
            Route::post('/ranks', [AdminController::class, 'storeRank'])->name('admin.catalogs.ranks.store');
            Route::post('/units', [AdminController::class, 'storeUnit'])->name('admin.catalogs.units.store');
            Route::post('/groups', [AdminController::class, 'storeGroup'])->name('admin.catalogs.groups.store');
            Route::post('/areas', [AdminController::class, 'storeArea'])->name('admin.catalogs.areas.store');
        });

        // User management
        Route::prefix('users')->group(function () {

            // List Of Users
            Route::get('/list', [AdminController::class, 'userManagement'])->name('admin.user.management');

            // Route For Adding User
            Route::get('/create-user', [AdminController::class, 'createUser'])->name('admin.user.management.add');

            Route::post('users', [AdminController::class, 'usersStore'])->name('admin.users.store');

            // Route For Downloading User Excel Sheet
            Route::get('/download-excel', [AdminController::class, 'downloadExcel'])->name('admin.download.excel');

            // Route For Getting Excel Sheet Data
            Route::post('/excel-import', [AdminController::class, 'getExcelData'])->name('admin.upload.excel');

            // Route For Reviewing Excel Sheet Data
            Route::get('/review-excel', [AdminController::class, 'reviewExcelData'])->name('admin.review.excel');

            // Route For Saving Excel Sheet Data
            Route::post('/save-excel', [AdminController::class, 'storeExcelData'])->name('admin.save.excel');

            // User Profile
            Route::get('/{id}', [AdminController::class, 'userProfile'])->name('admin.user.profile');

            // Route For Add User
            Route::get('/add', [AdminController::class, 'addUser'])->name('admin.user.add');

            Route::get('users/{id}', [AdminController::class, 'usersDestroy'])->name('admin.users.destroy');

            Route::get('/report/{id}', [AdminController::class, 'userReport'])->name('admin.user.report');

            // Register User In the Game
            Route::post('/register-in-game', [AdminController::class, 'registerUserInGame'])->name('admin.register.user.game');

            // Update Game Locale
            Route::post('/update-game-locale', [AdminController::class, 'updateGameLocale'])->name('admin.update.game.locale');
        });

        // List Games
        Route::prefix('games')->group(function () {

            // List Of Games
            Route::get('/list', [AdminController::class, 'listGames'])->name('admin.games.list');

        });
    });

    // Route::get('users', [AdminController::class, 'usersIndex'])->name('admin.users');

    // Route::get('users/edit/{id}', [AdminController::class, 'usersEdit'])->name('admin.users.edit');
    // Route::post('users/update/{id}', [AdminController::class, 'usersUpdate'])->name('admin.users.update');
    // Route::delete('users/{id}', [AdminController::class, 'usersDestroy'])->name('admin.users.destroy');
});
