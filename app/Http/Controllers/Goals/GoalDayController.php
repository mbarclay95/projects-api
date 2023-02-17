<?php

namespace App\Http\Controllers\Goals;

use App\Models\Goals\GoalDay;
use Mbarclay36\LaravelCrud\CrudController;

class GoalDayController extends CrudController
{
    protected static string $modelClass = GoalDay::class;

    protected static array $indexRules = [];
    protected static array $storeRules = [
        'amount' => 'int|required',
        'date' => 'date|required',
        'goalId' => 'int|required',
    ];
    protected static array $updateRules = [
        'amount' => 'int|required',
    ];
}
