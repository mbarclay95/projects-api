<?php

namespace App\Http\Controllers\Users;

use App\Models\Users\User;
use Mbarclay36\LaravelCrud\CrudController;

class UserController extends CrudController
{
    protected static string $modelClass = User::class;

    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
        'username' => 'required|string',
        'password' => 'required|string',
        'roles' => 'present|array',
        'userConfig.homePageRole' => 'nullable|string'
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'roles' => 'present|array',
        'userConfig.sideMenuOpen' => 'required|bool',
        'userConfig.homePageRole' => 'required|string'
    ];
}
