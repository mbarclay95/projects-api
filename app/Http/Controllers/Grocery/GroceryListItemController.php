<?php

namespace App\Http\Controllers\Grocery;

use App\Models\Grocery\GroceryListItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\CrudController;

class GroceryListItemController extends CrudController
{
    protected static string $modelClass = GroceryListItem::class;

    protected static array $storeRules = [
        'groceryItemId' => 'required|integer',
        'quantity' => 'nullable|numeric',
    ];

    protected static array $updateRules = [
        'quantity' => 'nullable|numeric',
        'bought' => 'required|boolean',
    ];

    /**
     * The CRUD package's default compares $model->user_id, a column
     * grocery_list_items does not have. The entry's user_group_id is the
     * actual authorization, for both overrides below.
     */
    public function cannotUpdate(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(GroceryListItem::updateForUserPermission())
            || $model->user_group_id !== $user->groceryGroup?->id;
    }

    public function cannotDestroy(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(GroceryListItem::deleteForUserPermission())
            || $model->user_group_id !== $user->groceryGroup?->id;
    }
}
