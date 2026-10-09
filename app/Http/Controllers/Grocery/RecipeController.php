<?php

namespace App\Http\Controllers\Grocery;

use App\Models\Grocery\Recipe;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\CrudController;

class RecipeController extends CrudController
{
    protected static string $modelClass = Recipe::class;

    protected static array $storeRules = [
        'name' => 'required|string',
        'description' => 'nullable|string',
        'ingredients' => 'array|present',
        'ingredients.*.groceryItemId' => 'required|integer',
        'ingredients.*.quantity' => 'nullable|numeric|gt:0',
    ];

    protected static array $updateRules = [
        'name' => 'required|string',
        'description' => 'nullable|string',
        'ingredients' => 'array|present',
        'ingredients.*.groceryItemId' => 'required|integer',
        'ingredients.*.quantity' => 'nullable|numeric|gt:0',
    ];

    /**
     * The CRUD package's default compares $model->user_id, a column
     * recipes does not have. The recipe's user_group_id is the
     * actual authorization, for both overrides below.
     */
    public function cannotUpdate(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(Recipe::updateForUserPermission())
            || $model->user_group_id !== $user->groceryGroup?->id;
    }

    public function cannotDestroy(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(Recipe::deleteForUserPermission())
            || $model->user_group_id !== $user->groceryGroup?->id;
    }
}
