<?php

namespace Database\Seeders;

use App\Enums\FeatureEnum;
use App\Enums\GroceryItemUnit;
use App\Models\Grocery\GroceryItem;
use App\Models\UserGroups\UserGroup;
use Illuminate\Database\Seeder;

class GroceryItemsSeeder extends Seeder
{
    private const ITEMS = [
        'Milk', 'Eggs', 'Butter', 'Cheddar Cheese', 'Yogurt',
        'Bread', 'Bagels', 'Tortillas',
        'Bananas', 'Apples', 'Oranges', 'Grapes', 'Avocados', 'Lemons',
        'Lettuce', 'Spinach', 'Tomatoes', 'Onions', 'Garlic', 'Potatoes',
        'Carrots', 'Bell Peppers', 'Broccoli', 'Cucumbers',
        'Chicken Breast', 'Ground Beef', 'Bacon', 'Salmon', 'Deli Turkey',
        'Rice', 'Pasta', 'Pasta Sauce', 'Cereal', 'Oatmeal',
        'Peanut Butter', 'Jelly', 'Flour', 'Sugar', 'Olive Oil',
        'Salt', 'Black Pepper', 'Ketchup', 'Mustard', 'Mayonnaise',
        'Canned Beans', 'Canned Tomatoes', 'Chicken Broth',
        'Coffee', 'Tea', 'Orange Juice', 'Sparkling Water',
        'Chips', 'Crackers', 'Granola Bars',
        'Frozen Vegetables', 'Frozen Pizza', 'Ice Cream',
        'Paper Towels', 'Toilet Paper', 'Dish Soap', 'Laundry Detergent', 'Trash Bags',
    ];

    /**
     * Seed common grocery items onto every grocery group's master list.
     *
     * @return void
     */
    public function run()
    {
        UserGroup::query()
            ->where('scope', '=', FeatureEnum::GROCERY->value)
            ->each(fn (UserGroup $group) => $this->seedGroup($group));
    }

    private function seedGroup(UserGroup $group): void
    {
        $existingNames = GroceryItem::query()
            ->where('user_group_id', '=', $group->id)
            ->pluck('name')
            ->map(fn (string $name) => strtolower($name))
            ->all();

        foreach (self::ITEMS as $name) {
            if (in_array(strtolower($name), $existingNames, true)) {
                continue;
            }

            $groceryItem = new GroceryItem([
                'name' => $name,
                'user_group_id' => $group->id,
                'unit' => GroceryItemUnit::NONE,
                'default_quantity' => null,
            ]);
            $groceryItem->save();
        }
    }
}
