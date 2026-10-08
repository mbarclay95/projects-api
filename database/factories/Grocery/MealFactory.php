<?php

namespace Database\Factories\Grocery;

use App\Enums\MealSlot;
use App\Models\Grocery\Meal;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meal>
 */
class MealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'user_group_id' => 1,
            'date' => '2026-10-12',
            'slot' => MealSlot::DINNER,
            'position' => 0,
            'name' => 'testing',
            'recipe_id' => null,
        ];
    }
}
