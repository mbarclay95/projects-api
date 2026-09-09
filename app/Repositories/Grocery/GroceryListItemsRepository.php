<?php

namespace App\Repositories\Grocery;

use App\Enums\GroceryItemUnit;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryListItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GroceryListItemsRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        if (! $user->groceryGroup) {
            return Collection::make();
        }

        return GroceryListItem::query()
            ->where('user_group_id', '=', $user->groceryGroup->id)
            ->whereNull('bought_at')
            ->with('groceryItem.tags', 'addedBy')
            ->orderBy(GroceryItem::query()->select('name')->whereColumn('id', 'grocery_list_items.grocery_item_id'))
            ->get();
    }

    public function createEntity($request, Authenticatable $user): Model|array
    {
        if (! $user->groceryGroup) {
            throw ValidationException::withMessages(['groceryItemId' => 'You are not in a grocery group.']);
        }

        $item = GroceryItem::query()
            ->where('id', '=', $request['groceryItemId'])
            ->where('user_group_id', '=', $user->groceryGroup->id)
            ->first();

        if (! $item) {
            throw ValidationException::withMessages(['groceryItemId' => 'That item is not on your master list.']);
        }

        $this->assertNotAlreadyOnTheList($item->id, $user->groceryGroup->id);

        $entry = new GroceryListItem([
            'grocery_item_id' => $item->id,
            'user_group_id' => $user->groceryGroup->id,
            'added_by_user_id' => $user->id,
            'quantity' => $this->quantityForUnit($item->unit, $request['quantity'] ?? null),
        ]);
        $entry->save();

        return $entry;
    }

    /**
     * @param  GroceryListItem  $model
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->quantity = $this->quantityForUnit($model->groceryItem->unit, $request['quantity'] ?? null);
        $model->bought_at = $request['bought'] ? ($model->bought_at ?? now()) : null;
        $model->save();

        return $model;
    }

    private function assertNotAlreadyOnTheList(int $groceryItemId, int $userGroupId): void
    {
        $alreadyOnTheList = GroceryListItem::query()
            ->where('grocery_item_id', '=', $groceryItemId)
            ->where('user_group_id', '=', $userGroupId)
            ->whereNull('bought_at')
            ->exists();

        if ($alreadyOnTheList) {
            throw ValidationException::withMessages(['groceryItemId' => 'That item is already on the shopping list.']);
        }
    }

    private function quantityForUnit(GroceryItemUnit $unit, ?float $quantity): ?float
    {
        return $unit === GroceryItemUnit::NONE ? null : $quantity;
    }
}
