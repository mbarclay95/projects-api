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
use App\Http\Controllers\FileExplorer\DirectoryItemController;
use App\Http\Controllers\Goals\GoalController;
use App\Http\Controllers\Goals\GoalDayController;
use App\Http\Controllers\Logging\LoggingController;
use App\Http\Controllers\Tasks\FamilyController;
use App\Http\Controllers\Tasks\TagController;
use App\Http\Controllers\Tasks\TaskController;
use App\Http\Controllers\Tasks\TaskUserConfigController;
use App\Http\Controllers\Users\RoleController;
use App\Http\Controllers\Users\UserController;
use Illuminate\Http\JsonResponse;
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
    return new JsonResponse(['success' => true]);
});

// LOGIN
Route::controller(AuthController::class)->group(function () {
    Route::middleware('auth')->group(function () {
        Route::get('/me', 'me');
        Route::post('/change-password', 'changePassword');
        Route::patch('/update-me', 'updateMe');
    });
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
    Route::apiResource('goal-days', GoalDayController::class)->except('index', 'show');
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
    Route::apiResource('task-user-config', TaskUserConfigController::class)->only('index', 'update');
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

// FILE EXPLORER
Route::middleware('auth')->prefix('file-explorer')->group(function () {
    Route::apiResource('directory-items', DirectoryItemController::class)->only('index', 'store');
    Route::patch('directory-items', [DirectoryItemController::class, 'update']);
    Route::patch('directory-items/delete', [DirectoryItemController::class, 'destroy']);
});

// LOGGING
Route::controller(LoggingController::class)->group(function () {
    Route::post('log-smartctl', 'logSmartResults');
    Route::get('validate-smartctl-logs', 'validateSmartLogs');
});
