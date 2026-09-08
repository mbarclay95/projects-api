<?php

namespace App\Http\Controllers\Tags;

use App\Models\Tags\Tag;
use Mbarclay36\LaravelCrud\CrudController;

class TagController extends CrudController
{
    protected static string $modelClass = Tag::class;

    protected static array $indexRules = [];
}
