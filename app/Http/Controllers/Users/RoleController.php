<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\ApiCrudController;
use App\Models\ApiModels\RoleApiModel;

class RoleController extends ApiCrudController
{
    protected static string $modelClass = RoleApiModel::class;

    protected static array $indexRules = [];
    protected static array $storeRules = [];
    protected static array $updateRules = [];
}
