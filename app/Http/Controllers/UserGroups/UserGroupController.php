<?php

namespace App\Http\Controllers\UserGroups;

use App\Enums\FeatureEnum;
use App\Models\UserGroups\UserGroup;
use Illuminate\Validation\Rule;
use Mbarclay36\LaravelCrud\CrudController;

class UserGroupController extends CrudController
{
    protected static string $modelClass = UserGroup::class;

    protected static array $indexRules = [];

    protected static array $storeRules = [];

    protected static array $updateRules = [
        'name' => 'required|string',
        'members' => 'present|array',
        'taskStrategy' => 'nullable|string',
        'taskPoints' => 'nullable|array',
    ];

    public function __construct()
    {
        static::$storeRules = [
            'name' => 'required|string',
            'members' => 'required|array',
            'scope' => ['required', 'string', Rule::in(FeatureEnum::groupScopeValues())],
            'taskStrategy' => 'nullable|string',
            'taskPoints' => 'nullable|array',
        ];
    }
}
