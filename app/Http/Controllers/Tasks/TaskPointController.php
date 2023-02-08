<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\ApiCrudController;
use App\Models\Tasks\TaskPoint;

class TaskPointController extends ApiCrudController
{
    protected static string $modelClass = TaskPoint::class;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
        'familyId' => 'required|int',
        'points' => 'int|required|min:0',
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
    ];
}
