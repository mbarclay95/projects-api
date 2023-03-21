<?php

namespace App\Http\Controllers\Tasks;

use App\Models\Tasks\TaskUserConfig;
use Mbarclay36\LaravelCrud\CrudController;

class TaskUserConfigController extends CrudController
{
    protected static string $modelClass = TaskUserConfig::class;
    protected static array $indexRules = [
        'familyId' => 'int|required',
        'weekOffset' => 'int|required'
    ];
    protected static array $storeRules = [
    ];
    protected static array $updateRules = [
        'tasksPerWeek' => 'required|int',
    ];
}
