<?php

namespace App\Http\Controllers\Grocery;

use App\Models\Grocery\Meal;
use App\Repositories\Grocery\MealsRepository;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Mbarclay36\LaravelCrud\CrudController;

class MealController extends CrudController
{
    protected static string $modelClass = Meal::class;

    protected static array $indexRules = [
        'weekStart' => 'required|date_format:Y-m-d',
    ];

    /**
     * CrudController only guards its own verbs, so this route authorises by
     * hand.
     */
    public function updateDay(Request $request, string $date): JsonResponse
    {
        /** @var Authenticatable $user */
        $user = Auth::user();

        if (! $user->hasPermissionTo(Meal::updateForUserPermission())) {
            throw new AuthenticationException;
        }

        Validator::make(['date' => $date], ['date' => 'required|date_format:Y-m-d'])->validate();

        $validated = $request->validate([
            'meals' => 'array|present',
            'meals.*.slot' => 'required|in:breakfast,lunch,dinner',
            'meals.*.name' => 'required|string|max:255',
        ]);

        return new JsonResponse(Meal::toApiModels((new MealsRepository)->replaceDay($date, $validated['meals'], $user)));
    }
}
