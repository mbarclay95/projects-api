<?php

namespace App\Http\Controllers\Grocery;

use App\Enums\GroceryItemUnit;
use App\Models\Grocery\GroceryItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;
use Mbarclay36\LaravelCrud\CrudController;

class GroceryItemController extends CrudController
{
    protected static string $modelClass = GroceryItem::class;

    protected static array $storeRules = [];

    protected static array $updateRules = [];

    public function __construct()
    {
        $rules = [
            'name' => 'required|string',
            'notes' => 'nullable|string',
            'tags' => 'array|present',
            'unit' => ['required', 'string', Rule::enum(GroceryItemUnit::class)],
            'defaultQuantity' => 'nullable|numeric',
            'category' => 'nullable|string',
        ];

        static::$storeRules = $rules;
        static::$updateRules = $rules;
    }

    /**
     * The CRUD package's default compares $model->user_id, a column
     * grocery_items does not have. The item's user_group_id is the actual
     * authorization, for both overrides below.
     */
    public function cannotUpdate(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(GroceryItem::updateForUserPermission())
            || $model->user_group_id !== $user->groceryGroup?->id;
    }

    public function cannotDestroy(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(GroceryItem::deleteForUserPermission())
            || $model->user_group_id !== $user->groceryGroup?->id;
    }
}
