<?php

use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;

Route::get('/', [IndexController::class, 'home'])->name('home');

Route::get('/launcher', [IndexController::class, 'launcher'])->name('launcher');

Route::get('/start-game/', [IndexController::class, 'startGame'])->name('start.game');

/***** Including Admin Routes *****/
include('admin.php');
/***** End Including Admin Routes *****/
