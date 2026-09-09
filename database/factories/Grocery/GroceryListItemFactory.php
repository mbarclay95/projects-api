<?php

namespace Database\Factories\Grocery;

use App\Models\Grocery\GroceryListItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroceryListItem>
 */
class GroceryListItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'quantity' => null,
            'bought_at' => null,
        ];
    }
}
