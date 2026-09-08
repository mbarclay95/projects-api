<?php

namespace App\Http\Controllers\Tasks;

use App\Models\ApiModels\FamilyMemberStatsApiModel;
use Mbarclay36\LaravelCrud\CrudController;

class FamilyStatsController extends CrudController
{
    protected static string $modelClass = FamilyMemberStatsApiModel::class;

    protected static array $indexRules = [
        'userGroupId' => 'required|int',
        'year' => 'required|int',
    ];

    protected static array $storeRules = [];

    protected static array $updateRules = [];
}
