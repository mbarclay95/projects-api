<?php

namespace App\Http\Controllers\Grocery;

use App\Models\Grocery\GroceryCategory;
use Mbarclay36\LaravelCrud\CrudController;

class GroceryCategoryController extends CrudController
{
    protected static string $modelClass = GroceryCategory::class;
}
