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
            Route::post('/sync-cognifit', [AdminMetricsController::class, 'syncCognifit'])->name('admin.metrics.sync-cognifit');
            Route::get('/comparative', [AdminMetricsController::class, 'comparative'])->name('admin.metrics.comparative');
            Route::get('/users/{user}', [AdminMetricsController::class, 'user'])->name('admin.metrics.user');
            Route::post('/users/{user}/metrics', [AdminMetricsController::class, 'storeUserMetric'])->name('admin.metrics.user.store');
            Route::post('/users/{user}/sync-cognifit', [AdminMetricsController::class, 'syncUserCognifit'])->name('admin.metrics.user.sync-cognifit');
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
            Route::get('/', [AdminController::class, 'userManagement'])->name('admin.user.management');

            Route::get('/create', [AdminController::class, 'createUser'])->name('admin.user.management.add');
            Route::post('/', [AdminController::class, 'usersStore'])->name('admin.users.store');

            Route::get('/excel/download', [AdminController::class, 'downloadExcel'])->name('admin.download.excel');
            Route::post('/excel/import', [AdminController::class, 'getExcelData'])->name('admin.upload.excel');
            Route::get('/excel/review', [AdminController::class, 'reviewExcelData'])->name('admin.review.excel');
            Route::post('/excel/save', [AdminController::class, 'storeExcelData'])->name('admin.save.excel');

            Route::get('/{id}/report', [AdminController::class, 'userReport'])->name('admin.user.report');
            Route::post('/{id}/register-cognifit', [AdminController::class, 'registerUserInGame'])->name('admin.register.user.game');
            Route::put('/{id}/locale', [AdminController::class, 'updateGameLocale'])->name('admin.update.game.locale');
            Route::put('/{id}', [AdminController::class, 'usersUpdate'])->name('admin.users.update');
            Route::delete('/{id}', [AdminController::class, 'usersDestroy'])->name('admin.users.destroy');
            Route::get('/{id}', [AdminController::class, 'userProfile'])->name('admin.user.profile');
        });

        // List Games
        Route::prefix('games')->group(function () {

            // List Of Games
            Route::get('/list', [AdminController::class, 'listGames'])->name('admin.games.list');

        });
    });
});
