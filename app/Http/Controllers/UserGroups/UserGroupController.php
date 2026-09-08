<?php

namespace App\Http\Controllers\UserGroups;

use App\Models\UserGroups\UserGroup;
use Mbarclay36\LaravelCrud\CrudController;

class UserGroupController extends CrudController
{
    protected static string $modelClass = UserGroup::class;

    protected static array $indexRules = [];

    protected static array $storeRules = [
        'name' => 'required|string',
        'members' => 'required|array',
        'taskStrategy' => 'required|string',
        'taskPoints' => 'nullable|array',
    ];

    protected static array $updateRules = [
        'name' => 'required|string',
        'members' => 'present|array',
        'taskStrategy' => 'required|string',
        'taskPoints' => 'nullable|array',
    ];
}
