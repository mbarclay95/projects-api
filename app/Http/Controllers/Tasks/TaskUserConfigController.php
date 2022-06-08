<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\ApiCrudController;
use App\Models\Tasks\TaskUserConfig;

class TaskUserConfigController extends ApiCrudController
{
    protected static string $modelClass = TaskUserConfig::class;
    protected static bool $getUserEntitiesOnly = false;
    protected static bool $updateUserEntityOnly = false;
    protected static array $indexRules = [];
    protected static array $storeRules = [
    ];
    protected static array $updateRules = [
        'tasksPerWeek' => 'required|int',
        'color' => 'required|string',
    ];
}
