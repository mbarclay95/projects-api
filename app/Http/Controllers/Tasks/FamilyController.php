<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\ApiCrudController;
use App\Models\Tasks\Family;

class FamilyController extends ApiCrudController
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
