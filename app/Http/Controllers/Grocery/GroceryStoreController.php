<?php

namespace App\Http\Controllers\Grocery;

use App\Models\Grocery\GroceryStore;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\CrudController;

class GroceryStoreController extends CrudController
{
    protected static string $modelClass = GroceryStore::class;

    protected static array $storeRules = [
        'name' => 'required|string',
        'categoryOrder' => 'array|present',
        'categoryOrder.*' => 'integer',
    ];

    protected static array $updateRules = [
        'name' => 'required|string',
        'categoryOrder' => 'array|present',
        'categoryOrder.*' => 'integer',
    ];

    /**
     * The CRUD package's default compares $model->user_id, a column
     * grocery_stores does not have. The store's user_group_id is the
     * actual authorization, for both overrides below.
     */
    public function cannotUpdate(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(GroceryStore::updateForUserPermission())
            || $model->user_group_id !== $user->groceryGroup?->id;
    }

    public function cannotDestroy(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(GroceryStore::deleteForUserPermission())
            || $model->user_group_id !== $user->groceryGroup?->id;
    }
}
