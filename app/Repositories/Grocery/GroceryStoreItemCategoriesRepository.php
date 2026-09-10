<?php

namespace App\Repositories\Grocery;

use App\Models\Grocery\GroceryCategory;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryStore;
use App\Models\Grocery\GroceryStoreItemCategory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GroceryStoreItemCategoriesRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        if (! $user->groceryGroup) {
            return Collection::make();
        }

        return GroceryStoreItemCategory::query()
            ->where('user_group_id', '=', $user->groceryGroup->id)
            ->get();
    }

    public function createEntity($request, Authenticatable $user): Model|array
    {
        if (! $user->groceryGroup) {
            throw ValidationException::withMessages(['groceryStoreId' => 'You are not in a grocery group.']);
        }

        $userGroupId = $user->groceryGroup->id;

        $this->assertBelongsToGroup(GroceryStore::class, $request['groceryStoreId'], $userGroupId, 'groceryStoreId', 'That store is not yours.');
        $this->assertBelongsToGroup(GroceryItem::class, $request['groceryItemId'], $userGroupId, 'groceryItemId', 'That item is not on your master list.');
        $this->assertBelongsToGroup(GroceryCategory::class, $request['groceryCategoryId'], $userGroupId, 'groceryCategoryId', 'That category is not yours.');
        $this->assertNoExistingException($request['groceryStoreId'], $request['groceryItemId'], $userGroupId);

        $exception = new GroceryStoreItemCategory([
            'user_group_id' => $userGroupId,
            'grocery_store_id' => $request['groceryStoreId'],
            'grocery_item_id' => $request['groceryItemId'],
            'grocery_category_id' => $request['groceryCategoryId'],
        ]);
        $exception->save();

        return $exception;
    }

    /**
     * @param  GroceryStoreItemCategory  $model
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $this->assertBelongsToGroup(GroceryCategory::class, $request['groceryCategoryId'], $model->user_group_id, 'groceryCategoryId', 'That category is not yours.');

        $model->grocery_category_id = $request['groceryCategoryId'];
        $model->save();

        return $model;
    }

    private function assertBelongsToGroup(string $modelClass, int $id, int $userGroupId, string $field, string $message): void
    {
        $exists = $modelClass::query()
            ->where('id', '=', $id)
            ->where('user_group_id', '=', $userGroupId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([$field => $message]);
        }
    }

    private function assertNoExistingException(int $groceryStoreId, int $groceryItemId, int $userGroupId): void
    {
        $alreadyExists = GroceryStoreItemCategory::query()
            ->where('user_group_id', '=', $userGroupId)
            ->where('grocery_store_id', '=', $groceryStoreId)
            ->where('grocery_item_id', '=', $groceryItemId)
            ->exists();

        if ($alreadyExists) {
            throw ValidationException::withMessages(['groceryItemId' => 'That item already has an exception at this store.']);
        }
    }
}
