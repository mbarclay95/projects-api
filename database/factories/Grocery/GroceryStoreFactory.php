<?php

namespace Database\Factories\Grocery;

use App\Models\Grocery\GroceryStore;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroceryStore>
 */
class GroceryStoreFactory extends Factory
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
            'category_order' => [],
        ];
    }
}
