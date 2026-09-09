<?php

namespace App\Repositories\Grocery;

use App\Enums\GroceryItemUnit;
use App\Models\Grocery\GroceryItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GroceryItemsRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        if (! $user->groceryGroup) {
            return Collection::make();
        }

        return GroceryItem::query()
            ->where('user_group_id', '=', $user->groceryGroup->id)
            ->with('tags')
            ->orderBy('name')
            ->get();
    }

    public function createEntity($request, Authenticatable $user): Model|array
    {
        if (! $user->groceryGroup) {
            throw ValidationException::withMessages(['name' => 'You are not in a grocery group.']);
        }

        $this->assertNameIsUnique($request['name'], $user->groceryGroup->id);
        $this->assertQuantityMatchesUnit($request['unit'], $request['defaultQuantity'] ?? null);

        $item = new GroceryItem([
            'name' => $request['name'],
            'notes' => $request['notes'] ?? null,
            'user_group_id' => $user->groceryGroup->id,
            'unit' => $request['unit'],
            'default_quantity' => $request['defaultQuantity'] ?? null,
        ]);
        $item->save();
        $item->updateTags($request['tags']);

        return $item;
    }

    /**
     * @param  GroceryItem  $model
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $this->assertNameIsUnique($request['name'], $model->user_group_id, $model->id);
        $this->assertQuantityMatchesUnit($request['unit'], $request['defaultQuantity'] ?? null);

        $model->name = $request['name'];
        $model->notes = $request['notes'] ?? null;
        $model->unit = $request['unit'];
        $model->default_quantity = $request['defaultQuantity'] ?? null;
        $model->updateTags($request['tags']);
        $model->save();

        return $model;
    }

    private function assertNameIsUnique(string $name, int $userGroupId, ?int $ignoreId = null): void
    {
        $duplicateExists = GroceryItem::query()
            ->where('user_group_id', '=', $userGroupId)
            ->whereRaw('lower(name) = ?', [strtolower($name)])
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages(['name' => 'That item is already on the master list.']);
        }
    }

    private function assertQuantityMatchesUnit(string $unit, ?float $defaultQuantity): void
    {
        if ($unit === GroceryItemUnit::NONE->value && $defaultQuantity !== null) {
            throw ValidationException::withMessages([
                'defaultQuantity' => 'An item with no unit can\'t have a default quantity.',
            ]);
        }
    }
}
