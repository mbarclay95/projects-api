<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\ApiCrudController;
use App\Models\Tasks\Tag;

class TagController extends ApiCrudController
{
    protected static string $modelClass = Tag::class;
    protected static bool $getUserEntitiesOnly = true;
    protected static array $indexRules = [];
}
