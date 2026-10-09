<?php

namespace App\Repositories\Grocery;

use App\Models\Grocery\Meal;
use App\Models\Grocery\Recipe;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mbarclay36\LaravelCrud\DefaultRepository;

class MealsRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        if (! $user->groceryGroup) {
            return Collection::make();
        }

        $weekEnd = Carbon::createFromFormat('Y-m-d', $request['weekStart'])->addDays(6)->toDateString();

        return Meal::query()
            ->where('user_group_id', '=', $user->groceryGroup->id)
            ->whereBetween('date', [$request['weekStart'], $weekEnd])
            ->orderBy('date')
            ->orderBy('position')
            ->orderBy('id')
            ->get();
    }

    public function replaceDay(string $date, array $meals, User $user): Collection
    {
        if (! $user->groceryGroup) {
            throw ValidationException::withMessages(['meals' => 'You are not in a grocery group.']);
        }

        $userGroupId = $user->groceryGroup->id;

        return DB::transaction(function () use ($date, $meals, $userGroupId) {
            Meal::query()
                ->where('user_group_id', '=', $userGroupId)
                ->where('date', '=', $date)
                ->delete();

            $recipeIds = Recipe::query()
                ->where('user_group_id', '=', $userGroupId)
                ->get(['id', 'name'])
                ->mapWithKeys(fn (Recipe $recipe) => [mb_strtolower($recipe->name) => $recipe->id]);

            $positions = [];
            foreach ($meals as $dish) {
                $name = trim($dish['name']);
                $position = $positions[$dish['slot']] ?? 0;
                $positions[$dish['slot']] = $position + 1;

                Meal::query()->create([
                    'user_group_id' => $userGroupId,
                    'date' => $date,
                    'slot' => $dish['slot'],
                    'position' => $position,
                    'name' => $name,
                    'recipe_id' => $recipeIds[mb_strtolower($name)] ?? null,
                ]);
            }

            return Meal::query()
                ->where('user_group_id', '=', $userGroupId)
                ->where('date', '=', $date)
                ->orderBy('position')
                ->orderBy('id')
                ->get();
        });
    }
}
