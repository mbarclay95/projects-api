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
        ['name' => 'Milk', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Eggs', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Butter', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Cheddar Cheese', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Yogurt', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Bread', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Bagels', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Tortillas', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Bananas', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Apples', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Oranges', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Grapes', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Avocados', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Lemons', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Lettuce', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Spinach', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Tomatoes', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Onions', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Garlic', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Potatoes', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Carrots', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Bell Peppers', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Broccoli', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Cucumbers', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Chicken Breast', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Ground Beef', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Bacon', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Salmon', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Deli Turkey', 'unit' => GroceryItemUnit::WEIGHT],
        ['name' => 'Rice', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Pasta', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Pasta Sauce', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Cereal', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Oatmeal', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Peanut Butter', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Jelly', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Flour', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Sugar', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Olive Oil', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Salt', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Black Pepper', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Ketchup', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Mustard', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Mayonnaise', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Canned Beans', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Canned Tomatoes', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Chicken Broth', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Coffee', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Tea', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Orange Juice', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Sparkling Water', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Chips', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Crackers', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Granola Bars', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Frozen Vegetables', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Frozen Pizza', 'unit' => GroceryItemUnit::COUNT],
        ['name' => 'Ice Cream', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Paper Towels', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Toilet Paper', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Dish Soap', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Laundry Detergent', 'unit' => GroceryItemUnit::NONE],
        ['name' => 'Trash Bags', 'unit' => GroceryItemUnit::NONE],
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

        foreach (self::ITEMS as $item) {
            if (in_array(strtolower($item['name']), $existingNames, true)) {
                continue;
            }

            $groceryItem = new GroceryItem([
                'name' => $item['name'],
                'user_group_id' => $group->id,
                'unit' => $item['unit'],
                'default_quantity' => null,
            ]);
            $groceryItem->save();
        }
    }
}
