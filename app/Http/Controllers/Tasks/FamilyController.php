<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\ApiCrudController;
use App\Models\Tasks\Family;

class FamilyController extends ApiCrudController
{
    protected static string $modelClass = Family::class;
    protected static array $indexRules = [];
    protected static bool $getUserEntitiesOnly = false;
    protected static array $storeRules = [
        'name' => 'required|string'
    ];
    protected static array $updateRules = [];
}
