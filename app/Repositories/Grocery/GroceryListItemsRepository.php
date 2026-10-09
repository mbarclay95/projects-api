<?php

namespace App\Repositories\Grocery;

use App\Enums\GroceryItemUnit;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryListItem;
use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
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
            ->with('groceryItem.tags', 'groceryItem.groceryCategory', 'addedBy')
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

    /**
     * @param  array<int, array{groceryItemId: int, quantity?: float|int|string|null}>  $items
     * @return array{added: int, alreadyOnList: int}
     */
    public function addBatch(array $items, User $user): array
    {
        $group = $user->groceryGroup;

        if (! $group) {
            throw ValidationException::withMessages(['items' => 'You are not in a grocery group.']);
        }

        $itemIds = array_column($items, 'groceryItemId');
        $groceryItems = GroceryItem::query()
            ->where('user_group_id', '=', $group->id)
            ->whereIn('id', $itemIds)
            ->get()
            ->keyBy('id');

        if ($groceryItems->count() < count($itemIds)) {
            throw ValidationException::withMessages(['items' => 'That item is not on your master list.']);
        }

        return DB::transaction(function () use ($items, $groceryItems, $group, $user) {
            $added = 0;
            $alreadyOnList = 0;

            foreach ($items as $requested) {
                $item = $groceryItems[$requested['groceryItemId']];
                $amount = $item->unit === GroceryItemUnit::NONE
                    ? null
                    : ($requested['quantity'] ?? $item->default_quantity);

                $entry = GroceryListItem::query()
                    ->where('user_group_id', '=', $group->id)
                    ->where('grocery_item_id', '=', $item->id)
                    ->whereNull('bought_at')
                    ->first();

                if (! $entry) {
                    GroceryListItem::query()->create([
                        'grocery_item_id' => $item->id,
                        'user_group_id' => $group->id,
                        'added_by_user_id' => $user->id,
                        'quantity' => $amount,
                    ]);
                    $added++;

                    continue;
                }

                if ($amount !== null) {
                    $entry->quantity = ($entry->quantity ?? 0) + $amount;
                    $entry->save();
                }
                $alreadyOnList++;
            }

            return ['added' => $added, 'alreadyOnList' => $alreadyOnList];
        });
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
