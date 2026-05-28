<?php

use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'home'])->name('home');

Route::get('/login', [IndexController::class, 'showLogin'])->name('user.login');
Route::post('/login', [IndexController::class, 'login'])->name('user.login.submit');
Route::post('/logout', [IndexController::class, 'logout'])->name('user.logout');
Route::get('/perfil-inicial', [IndexController::class, 'showOnboarding'])->name('user.onboarding');
Route::post('/perfil-inicial', [IndexController::class, 'completeOnboarding'])->name('user.onboarding.submit');
Route::get('/perfil', [IndexController::class, 'profile'])->name('user.profile');
Route::get('/simuladores', [IndexController::class, 'games'])->name('user.games');
Route::get('/juegos', fn () => redirect()->route('user.games'));

Route::get('/launcher', [IndexController::class, 'launcher'])->name('launcher');

Route::get('/start-game/', [IndexController::class, 'startGame'])->name('start.game');
Route::post('/cognifit/users/{user}/sync-session', [IndexController::class, 'syncCognifitSession'])->name('cognifit.session.sync');

/***** Including Admin Routes *****/
require __DIR__.'/admin.php';
/***** End Including Admin Routes *****/
