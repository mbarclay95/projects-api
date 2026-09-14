<?php

namespace App\Repositories\Grocery;

use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryStore;
use App\Models\Grocery\GroceryStoreUnavailableItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GroceryStoreUnavailableItemsRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        if (! $user->groceryGroup) {
            return Collection::make();
        }

        return GroceryStoreUnavailableItem::query()
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
        $this->assertNoExistingRow($request['groceryStoreId'], $request['groceryItemId'], $userGroupId);

        $unavailableItem = new GroceryStoreUnavailableItem([
            'user_group_id' => $userGroupId,
            'grocery_store_id' => $request['groceryStoreId'],
            'grocery_item_id' => $request['groceryItemId'],
        ]);
        $unavailableItem->save();

        return $unavailableItem;
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

    private function assertNoExistingRow(int $groceryStoreId, int $groceryItemId, int $userGroupId): void
    {
        $alreadyExists = GroceryStoreUnavailableItem::query()
            ->where('user_group_id', '=', $userGroupId)
            ->where('grocery_store_id', '=', $groceryStoreId)
            ->where('grocery_item_id', '=', $groceryItemId)
            ->exists();

        if ($alreadyExists) {
            throw ValidationException::withMessages(['groceryItemId' => 'That item is already marked unavailable at this store.']);
        }
    }
}
