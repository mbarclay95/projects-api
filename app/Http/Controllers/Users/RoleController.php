<?php

namespace App\Http\Controllers\Users;

use App\Models\ApiModels\RoleApiModel;
use Illuminate\Contracts\Auth\Authenticatable;
use Mbarclay36\LaravelCrud\CrudController;

class RoleController extends CrudController
{
    protected static string $modelClass = RoleApiModel::class;

    protected static array $indexRules = [];
    protected static array $storeRules = [];
    protected static array $updateRules = [];

    // Anyone can hit this route. Check admin permission in repository
    public function cannotIndex(Authenticatable $user): bool
    {
        return false;
    }
}
