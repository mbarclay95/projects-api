<?php

namespace App\Http\Controllers\Tasks;

use App\Models\Tasks\Task;
use Mbarclay36\LaravelCrud\CrudController;

class TaskController extends CrudController
{
    protected static string $modelClass = Task::class;

    protected static array $indexRules = [
        'numOfDays' => 'int',
        'ownerType' => 'string',
        'ownerId' => 'int',
        'completedStatus' => 'string',
        'recurringType' => 'string',
//        'page' => 'int',
//        'pageSize' => 'int',
        'showPaused' => 'bool',
        'sort' => 'string',
        'sortDir' => 'string',
        'search' => 'string|nullable',
        'tags' => 'array|nullable'
    ];
    protected static array $storeRules = [
        'name' => 'required|string',
        'description' => 'present|string|nullable',
        'ownerType' => 'required|string',
//        'ownerId' => 'required|int',
        'recurring' => 'required|bool',
        'dueDate' => 'required|date',
        'frequencyAmount' => 'nullable|int',
        'frequencyUnit' => 'nullable|string',
        'tags' => 'array|present',
        'taskPoint' => 'nullable|int',
        'priority' => 'int|required',
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
        'tags' => 'array|present',
        'taskPoint' => 'nullable|int',
        'isActive' => 'required|bool',
        'priority' => 'int|required',
    ];
}
