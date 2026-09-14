<?php

namespace Database\Seeders;

use App\Enums\FeatureEnum;
use App\Enums\GroceryItemUnit;
use App\Models\Grocery\GroceryCategory;
use App\Models\Grocery\GroceryItem;
use App\Models\UserGroups\UserGroup;
use Illuminate\Database\Seeder;

class GroceryItemsSeeder extends Seeder
{
    private const ITEMS = [
        'Milk' => 'Dairy', 'Eggs' => 'Dairy', 'Butter' => 'Dairy', 'Cheddar Cheese' => 'Dairy', 'Yogurt' => 'Dairy',
        'Bread' => 'Bakery', 'Bagels' => 'Bakery', 'Tortillas' => 'Bakery',
        'Bananas' => 'Produce', 'Apples' => 'Produce', 'Oranges' => 'Produce', 'Grapes' => 'Produce',
        'Avocados' => 'Produce', 'Lemons' => 'Produce',
        'Lettuce' => 'Produce', 'Spinach' => 'Produce', 'Tomatoes' => 'Produce', 'Onions' => 'Produce',
        'Garlic' => 'Produce', 'Potatoes' => 'Produce',
        'Carrots' => 'Produce', 'Bell Peppers' => 'Produce', 'Broccoli' => 'Produce', 'Cucumbers' => 'Produce',
        'Chicken Breast' => 'Meat & Seafood', 'Ground Beef' => 'Meat & Seafood', 'Bacon' => 'Meat & Seafood',
        'Salmon' => 'Meat & Seafood', 'Deli Turkey' => 'Meat & Seafood',
        'Rice' => 'Pantry', 'Pasta' => 'Pantry', 'Pasta Sauce' => 'Pantry', 'Cereal' => 'Pantry', 'Oatmeal' => 'Pantry',
        'Peanut Butter' => 'Pantry', 'Jelly' => 'Pantry', 'Flour' => 'Pantry', 'Sugar' => 'Pantry', 'Olive Oil' => 'Pantry',
        'Salt' => 'Pantry', 'Black Pepper' => 'Pantry',
        'Ketchup' => 'Condiments', 'Mustard' => 'Condiments', 'Mayonnaise' => 'Condiments',
        'Canned Beans' => 'Pantry', 'Canned Tomatoes' => 'Pantry', 'Chicken Broth' => 'Pantry',
        'Coffee' => 'Beverages', 'Tea' => 'Beverages', 'Orange Juice' => 'Beverages', 'Sparkling Water' => 'Beverages',
        'Chips' => 'Snacks', 'Crackers' => 'Snacks', 'Granola Bars' => 'Snacks',
        'Frozen Vegetables' => 'Frozen', 'Frozen Pizza' => 'Frozen', 'Ice Cream' => 'Frozen',
        'Paper Towels' => 'Household', 'Toilet Paper' => 'Household', 'Dish Soap' => 'Household',
        'Laundry Detergent' => 'Household', 'Trash Bags' => 'Household',
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

    public function seedGroup(UserGroup $group): void
    {
        $existingNames = GroceryItem::query()
            ->where('user_group_id', '=', $group->id)
            ->pluck('name')
            ->map(fn (string $name) => strtolower($name))
            ->all();

        foreach (self::ITEMS as $name => $category) {
            if (in_array(strtolower($name), $existingNames, true)) {
                continue;
            }

            $groceryItem = new GroceryItem([
                'name' => $name,
                'user_group_id' => $group->id,
                'unit' => GroceryItemUnit::NONE,
                'default_quantity' => null,
                'grocery_category_id' => GroceryCategory::resolveForGroup($category, $group->id)?->id,
            ]);
            $groceryItem->save();
        }
    }
}
