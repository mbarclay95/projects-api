<?php

namespace App\Http\Controllers\Grocery;

use App\Models\Grocery\GroceryStoreUnavailableItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\CrudController;

class GroceryStoreUnavailableItemController extends CrudController
{
    protected static string $modelClass = GroceryStoreUnavailableItem::class;

    protected static array $storeRules = [
        'groceryStoreId' => 'required|integer',
        'groceryItemId' => 'required|integer',
    ];

    public function cannotDestroy(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(GroceryStoreUnavailableItem::deleteForUserPermission())
            || $model->user_group_id !== $user->groceryGroup?->id;
    }
}
