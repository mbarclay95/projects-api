<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\ApiCrudController;
use App\Models\Tasks\Task;

class TaskController extends ApiCrudController
{
    protected static string $modelClass = Task::class;
    protected static bool $updateUserEntityOnly = false;
    protected static bool $destroyUserEntityOnly = false;
    protected static array $indexRules = [
        'numOfDays' => 'int',
        'ownerType' => 'string',
        'ownerId' => 'int',
        'completedStatus' => 'string',
        'recurringType' => 'string',
        'page' => 'int',
        'pageSize' => 'int',
        'sort' => 'string',
        'sortDir' => 'string',
        'search' => 'string|nullable',
        'tags' => 'array|nullable'
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
        'tags' => 'array|present'
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
        'tags' => 'array|present'
    ];
}
