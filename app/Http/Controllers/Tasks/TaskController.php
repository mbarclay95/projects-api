<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\ApiCrudController;
use App\Models\Tasks\Task;

class TaskController extends ApiCrudController
{
    protected static string $modelClass = Task::class;
    protected static array $indexRules = [];
    protected static array $storeRules = [];
    protected static array $updateRules = [];
}
