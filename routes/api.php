<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Backups\BackupController;
use App\Http\Controllers\Backups\ScheduledBackupController;
use App\Http\Controllers\Backups\TargetController;
use App\Http\Controllers\Dashboard\FolderController;
use App\Http\Controllers\Dashboard\SiteController;
use App\Http\Controllers\Dashboard\SiteImageController;
use App\Http\Controllers\Events\EventController;
use App\Http\Controllers\Events\EventParticipantController;
use App\Http\Controllers\Goals\GoalController;
use App\Http\Controllers\Tasks\FamilyController;
use App\Http\Controllers\Tasks\TagController;
use App\Http\Controllers\Tasks\TaskController;
use App\Http\Controllers\Tasks\TaskPointController;
use App\Http\Controllers\Tasks\TaskUserConfigController;
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

// HEALTH CHECK
Route::get('health-check', function () {
    return new \Illuminate\Http\JsonResponse(['success' => true]);
});

// LOGIN
Route::controller(AuthController::class)->group(function () {
    Route::get('/me', 'me')->middleware('auth');
    Route::post('/login', 'login');
});

// USERS
Route::middleware('auth')->group(function () {
    Route::apiResource('users', UserController::class)->except('show');
    Route::apiResource('roles', RoleController::class)->only('index');
});

// GOALS
Route::middleware('auth')->group(function () {
    Route::apiResource('goals', GoalController::class)->except('show');
});

// BACKUPS
Route::middleware('auth')->group(function () {
    Route::apiResource('backups', BackupController::class)->only('index', 'create');
    Route::apiResource('scheduled-backups', ScheduledBackupController::class)->except('show');
    Route::apiResource('targets', TargetController::class)->except('show');
});

// TASKS
Route::middleware('auth')->group(function () {
    Route::apiResource('tasks', TaskController::class)->except('show');
    Route::apiResource('tags', TagController::class)->only('index');
    Route::apiResource('families', FamilyController::class);
    Route::apiResource('task-user-config', TaskUserConfigController::class)->only('update');
    Route::apiResource('task-points', TaskPointController::class)->only('store', 'update', 'destroy');
});

// DASHBOARD
Route::middleware('auth')->group(function () {
    Route::apiResource('folders', FolderController::class)->except('show');
    Route::patch('folder-sorts', [FolderController::class, 'updateFolderSorts']);
    Route::apiResource('sites', SiteController::class)->except('index', 'show');
    Route::patch('site-sorts', [SiteController::class, 'updateSiteSorts']);
    Route::apiResource('site-images', SiteImageController::class)->only('store');
});
Route::apiResource('site-images', SiteImageController::class)->only('show');

// EVENTS
Route::middleware('auth')->group(function () {
    Route::apiResource('events', EventController::class)->except('show');
    Route::apiResource('event-participants', EventParticipantController::class)->only('update');
});

