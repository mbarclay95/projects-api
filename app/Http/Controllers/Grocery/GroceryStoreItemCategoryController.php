<?php

namespace App\Http\Controllers\Grocery;

use App\Models\Grocery\GroceryStoreItemCategory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\CrudController;

class GroceryStoreItemCategoryController extends CrudController
{
    protected static string $modelClass = GroceryStoreItemCategory::class;

    protected static array $storeRules = [
        'groceryStoreId' => 'required|integer',
        'groceryItemId' => 'required|integer',
        'groceryCategoryId' => 'required|integer',
    ];

    protected static array $updateRules = [
        'groceryCategoryId' => 'required|integer',
    ];

    /**
     * The CRUD package's default compares $model->user_id, a column
     * grocery_store_item_categories does not have. The exception's
     * user_group_id is the actual authorization, for both overrides below.
     */
    public function cannotUpdate(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(GroceryStoreItemCategory::updateForUserPermission())
            || $model->user_group_id !== $user->groceryGroup?->id;
    }

    public function cannotDestroy(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(GroceryStoreItemCategory::deleteForUserPermission())
            || $model->user_group_id !== $user->groceryGroup?->id;
    }
}
