<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Backups\BackupController;
use App\Http\Controllers\Backups\TargetController;
use App\Http\Controllers\Goals\GoalController;
use App\Http\Controllers\Users\RoleController;
use App\Http\Controllers\Users\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(AuthController::class)->group(function () {
    Route::get('/me', 'me')->middleware('auth');
    Route::post('/login', 'login');
});

Route::middleware('auth')->group(function () {
    Route::apiResource('users', UserController::class)->except('show');
    Route::apiResource('roles', RoleController::class)->only('index');
});

Route::middleware('auth')->group(function () {
    Route::apiResource('goals', GoalController::class)->except('show');
});

Route::middleware('auth')->group(function () {
    Route::apiResource('backups', BackupController::class);
    Route::apiResource('targets', TargetController::class)->except('show');
});
