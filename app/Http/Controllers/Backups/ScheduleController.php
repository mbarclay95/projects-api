<?php

namespace App\Http\Controllers\Backups;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backups\ScheduledBackupStoreRequest;
use App\Models\Backups\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mbarclay36\LaravelCrud\CrudController;

class ScheduleController extends CrudController
{
    protected static string $modelClass = Schedule::class;
    protected static array $indexRules = [];
    protected static array $storeRules = [

    ];
    protected static array $updateRules = [];}
