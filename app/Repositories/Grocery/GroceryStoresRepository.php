<?php

namespace App\Repositories\Grocery;

use App\Models\Grocery\GroceryCategory;
use App\Models\Grocery\GroceryStore;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GroceryStoresRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        if (! $user->groceryGroup) {
            return Collection::make();
        }

        return GroceryStore::query()
            ->where('user_group_id', '=', $user->groceryGroup->id)
            ->orderBy('name')
            ->get();
    }

    public function createEntity($request, Authenticatable $user): Model|array
    {
        if (! $user->groceryGroup) {
            throw ValidationException::withMessages(['name' => 'You are not in a grocery group.']);
        }

        $this->assertNameIsUnique($request['name'], $user->groceryGroup->id);

        $store = new GroceryStore([
            'name' => $request['name'],
            'user_group_id' => $user->groceryGroup->id,
            'category_order' => $this->sanitiseCategoryOrder($request['categoryOrder'], $user->groceryGroup->id),
        ]);
        $store->save();

        return $store;
    }

    /**
     * @param  GroceryStore  $model
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $this->assertNameIsUnique($request['name'], $model->user_group_id, $model->id);

        $model->name = $request['name'];
        $model->category_order = $this->sanitiseCategoryOrder($request['categoryOrder'], $model->user_group_id);
        $model->save();

        return $model;
    }

    private function assertNameIsUnique(string $name, int $userGroupId, ?int $ignoreId = null): void
    {
        $duplicateExists = GroceryStore::query()
            ->where('user_group_id', '=', $userGroupId)
            ->whereRaw('lower(name) = ?', [strtolower($name)])
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages(['name' => 'That store already exists.']);
        }
    }

    private function sanitiseCategoryOrder(array $ids, int $userGroupId): array
    {
        $validIds = GroceryCategory::query()
            ->where('user_group_id', '=', $userGroupId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        $sanitised = [];
        foreach ($ids as $id) {
            if (in_array($id, $validIds) && ! in_array($id, $sanitised)) {
                $sanitised[] = $id;
            }
        }

        return $sanitised;
    }
}
