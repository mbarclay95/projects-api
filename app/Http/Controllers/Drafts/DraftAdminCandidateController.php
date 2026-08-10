<?php

namespace App\Http\Controllers\Drafts;

use App\Models\ApiModels\DraftAdminCandidateApiModel;
use Mbarclay36\LaravelCrud\CrudController;

class DraftAdminCandidateController extends CrudController
{
    protected static string $modelClass = DraftAdminCandidateApiModel::class;

    protected static array $indexRules = [];
}
