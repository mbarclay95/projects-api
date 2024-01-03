<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\ApiModels\FamilyMemberStatsApiModel;
use Illuminate\Http\Request;
use Mbarclay36\LaravelCrud\CrudController;

class FamilyStatsController extends CrudController
{
    protected static string $modelClass = FamilyMemberStatsApiModel::class;
    protected static array $indexRules = [
        'familyId' => 'required|int',
        'year' => 'required|int',
    ];
    protected static array $storeRules = [];
    protected static array $updateRules = [];
}
