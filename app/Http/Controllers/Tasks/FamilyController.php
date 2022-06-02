<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\ApiCrudController;
use App\Models\Tasks\Family;

class FamilyController extends ApiCrudController
{
    protected static string $modelClass = Family::class;
    protected static bool $getUserEntitiesOnly = false;
    protected static bool $updateUserEntityOnly = false;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
        'members' => 'required|array'
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'members' => 'required|array'
    ];
}
