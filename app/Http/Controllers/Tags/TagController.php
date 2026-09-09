<?php

namespace App\Http\Controllers\Tags;

use App\Enums\FeatureEnum;
use App\Models\Tags\Tag;
use Illuminate\Validation\Rule;
use Mbarclay36\LaravelCrud\CrudController;

class TagController extends CrudController
{
    protected static string $modelClass = Tag::class;

    protected static array $indexRules = [];

    public function __construct()
    {
        static::$indexRules = [
            'scope' => ['required', 'string', Rule::in(FeatureEnum::tagScopeValues())],
        ];
    }
}
