<?php

namespace App\Http\Controllers\Tasks;

use App\Models\Tasks\Tag;
use Mbarclay36\LaravelCrud\CrudController;

class TagController extends CrudController
{
    protected static string $modelClass = Tag::class;
    protected static array $indexRules = [];
}
