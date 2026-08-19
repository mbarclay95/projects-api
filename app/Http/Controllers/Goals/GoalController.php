<?php

namespace App\Http\Controllers\Goals;

use App\Models\Goals\Goal;
use Mbarclay36\LaravelCrud\CrudController;

class GoalController extends CrudController
{
    protected static string $modelClass = Goal::class;

    protected static array $indexRules = [
        'weekOffset' => 'int|nullable',
    ];

    protected static array $storeRules = [
        'title' => 'string|required',
        'verb' => 'string|required',
        'lengthOfTime' => 'string|required',
        'equality' => 'string|required',
        'expectedAmount' => 'int|required',
        'unit' => 'string|required',
    ];

    protected static array $updateRules = [
        'title' => 'string|required',
        'verb' => 'string|required',
        'lengthOfTime' => 'string|required',
        'equality' => 'string|required',
        'expectedAmount' => 'int|required',
        'unit' => 'string|required',
        'weekOffset' => 'int|required',
    ];
}
