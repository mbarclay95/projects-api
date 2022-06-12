<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\ApiCrudController;
use App\Models\Tasks\Task;

class TaskController extends ApiCrudController
{
    protected static string $modelClass = Task::class;
    protected static bool $updateUserEntityOnly = false;
    protected static array $indexRules = [
        'numOfDays' => 'int',
        'ownerType' => 'string',
        'ownerId' => 'int',
        'completedStatus' => 'string',
        'recurringType' => 'string',
        'page' => 'int',
        'pageSize' => 'int',
        'sort' => 'string',
        'sortDir' => 'string'
    ];
    protected static array $storeRules = [
        'name' => 'required|string',
        'description' => 'present|string|nullable',
        'ownerType' => 'required|string',
        'ownerId' => 'required|int',
        'recurring' => 'required|bool',
        'dueDate' => 'required|date',
        'frequencyAmount' => 'nullable|int',
        'frequencyUnit' => 'nullable|string',
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'description' => 'present|string|nullable',
        'ownerType' => 'required|string',
        'ownerId' => 'required|int',
        'recurring' => 'required|bool',
        'dueDate' => 'required|date',
        'frequencyAmount' => 'nullable|int',
        'frequencyUnit' => 'nullable|string',
        'completedAt' => 'nullable|date',
    ];
}
