<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\ApiCrudController;
use App\Models\Tasks\Task;

class TaskController extends ApiCrudController
{
    protected static string $modelClass = Task::class;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
        'description' => 'present|string',
        'ownerType' => 'required|string',
        'ownerId' => 'required|int',
        'recurring' => 'required|bool',
        'dueDate' => 'required|date',
        'frequencyAmount' => 'nullable|int',
        'frequencyUnit' => 'nullable|string',
    ];
    protected static array $updateRules = [];
}
