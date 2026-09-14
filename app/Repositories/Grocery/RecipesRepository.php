<?php

namespace App\Repositories\Grocery;

use App\Enums\GroceryItemUnit;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\Recipe;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mbarclay36\LaravelCrud\DefaultRepository;

class RecipesRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        if (! $user->groceryGroup) {
            return Collection::make();
        }

        return Recipe::query()
            ->where('user_group_id', '=', $user->groceryGroup->id)
            ->with('ingredients')
            ->orderBy('name')
            ->get();
    }

    public function createEntity($request, Authenticatable $user): Model|array
    {
        if (! $user->groceryGroup) {
            throw ValidationException::withMessages(['name' => 'You are not in a grocery group.']);
        }

        $userGroupId = $user->groceryGroup->id;
        $this->assertNameIsUnique($request['name'], $userGroupId);
        $this->assertIngredientsAreValid($request['ingredients'], $userGroupId);

        return DB::transaction(function () use ($request, $userGroupId) {
            $recipe = new Recipe([
                'name' => $request['name'],
                'description' => $request['description'] ?? null,
                'user_group_id' => $userGroupId,
            ]);
            $recipe->save();
            $this->writeIngredients($recipe, $request['ingredients']);

            return $recipe->load('ingredients');
        });
    }

    /**
     * @param  Recipe  $model
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $this->assertNameIsUnique($request['name'], $model->user_group_id, $model->id);
        $this->assertIngredientsAreValid($request['ingredients'], $model->user_group_id);

        return DB::transaction(function () use ($model, $request) {
            $model->name = $request['name'];
            $model->description = $request['description'] ?? null;
            $model->save();

            $model->ingredients()->delete();
            $this->writeIngredients($model, $request['ingredients']);

            return $model->load('ingredients');
        });
    }

    /**
     * @param  Recipe  $model
     */
    public function destroyEntity(Model $model, Authenticatable $user): bool
    {
        $model->ingredients()->delete();
        $model->delete();

        return true;
    }

    private function assertNameIsUnique(string $name, int $userGroupId, ?int $ignoreId = null): void
    {
        $duplicateExists = Recipe::query()
            ->where('user_group_id', '=', $userGroupId)
            ->whereRaw('lower(name) = ?', [strtolower($name)])
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($duplicateExists) {
            throw ValidationException::withMessages(['name' => 'That recipe already exists.']);
        }
    }

    private function assertIngredientsAreValid(array $ingredients, int $userGroupId): void
    {
        $itemIds = array_column($ingredients, 'groceryItemId');
        $uniqueItemIds = array_unique($itemIds);

        if (count($itemIds) !== count($uniqueItemIds)) {
            throw ValidationException::withMessages(['ingredients' => 'That item is already in this recipe.']);
        }

        $validItemCount = GroceryItem::query()
            ->where('user_group_id', '=', $userGroupId)
            ->whereIn('id', $uniqueItemIds)
            ->count();

        if ($validItemCount !== count($uniqueItemIds)) {
            throw ValidationException::withMessages(['ingredients' => 'That item is not on your master list.']);
        }
    }

    private function writeIngredients(Recipe $recipe, array $ingredients): void
    {
        $items = GroceryItem::query()
            ->whereIn('id', array_column($ingredients, 'groceryItemId'))
            ->get()
            ->keyBy('id');

        foreach ($ingredients as $ingredient) {
            $item = $items[$ingredient['groceryItemId']];
            $recipe->ingredients()->create([
                'grocery_item_id' => $ingredient['groceryItemId'],
                'quantity' => $item->unit === GroceryItemUnit::NONE ? null : ($ingredient['quantity'] ?? null),
            ]);
        }
    }
}
