<?php

namespace Database\Factories\Grocery;

use App\Models\Grocery\GroceryCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroceryCategory>
 */
class GroceryCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => 'testing',
            'user_group_id' => 1,
        ];
    }
}
