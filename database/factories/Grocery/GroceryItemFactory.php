<?php

namespace Database\Factories\Grocery;

use App\Models\Grocery\GroceryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroceryItem>
 */
class GroceryItemFactory extends Factory
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
            'notes' => null,
            'user_group_id' => 1,
        ];
    }
}
