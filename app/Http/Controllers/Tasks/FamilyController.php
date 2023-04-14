<?php

namespace App\Http\Controllers\Tasks;

use App\Models\Tasks\Family;
use Mbarclay36\LaravelCrud\CrudController;

class FamilyController extends CrudController
{
    protected static string $modelClass = Family::class;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
        'members' => 'required|array',
        'taskStrategy' => 'required|string',
        'taskPoints' => 'nullable|array'
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'members' => 'present|array',
        'taskStrategy' => 'required|string',
        'taskPoints' => 'nullable|array'
    ];
}
