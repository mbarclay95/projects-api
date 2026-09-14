<?php

namespace App\Repositories\Grocery;

use App\Models\Grocery\GroceryCategory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GroceryCategoriesRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        if (! $user->groceryGroup) {
            return Collection::make();
        }

        return GroceryCategory::query()
            ->where('user_group_id', '=', $user->groceryGroup->id)
            ->orderBy('name')
            ->get();
    }
}
