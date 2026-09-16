<?php

namespace Tests\Feature\Grocery;

use App\Enums\GroceryItemUnit;
use App\Enums\Roles;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\Recipe;
use App\Models\Grocery\RecipeItem;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RecipesTest extends TestCase
{
    private User $user;

    private UserGroup $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::GROCERY_ROLE);
        $this->group = UserGroup::factory()->grocery()->create();
        $this->group->members()->attach($this->user->id, ['scope' => 'grocery']);
    }

    public function test_the_index_returns_the_callers_groups_recipes_with_ingredients(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::COUNT]);
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id, 'name' => 'Tacos']);
        RecipeItem::factory()->create([
            'recipe_id' => $recipe->id,
            'grocery_item_id' => $item->id,
            'quantity' => 2,
        ]);

        $response = $this->jsonAs($this->user, 'GET', 'api/recipes')
            ->assertSuccessful()
            ->json();

        $found = collect($response)->firstWhere('id', $recipe->id);
        self::assertNotNull($found);
        self::assertCount(1, $found['ingredients']);
        self::assertEquals($item->id, $found['ingredients'][0]['groceryItemId']);
        self::assertEquals(2, $found['ingredients'][0]['quantity']);
    }

    public function test_the_index_excludes_another_grocery_groups_recipes(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherRecipe = Recipe::factory()->create(['user_group_id' => $otherGroup->id]);

        $ids = collect($this->jsonAs($this->user, 'GET', 'api/recipes')
            ->assertSuccessful()
            ->json())->pluck('id');

        self::assertFalse($ids->contains($otherRecipe->id));
    }

    public function test_the_index_is_empty_for_a_caller_with_no_grocery_group(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);

        $this->jsonAs($groupless, 'GET', 'api/recipes')
            ->assertSuccessful()
            ->assertJson([]);
    }

    public function test_a_post_from_a_caller_with_no_grocery_group_is_a_422(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);

        $this->jsonAs($groupless, 'POST', 'api/recipes', [
            'name' => 'Tacos',
            'description' => null,
            'ingredients' => [],
        ])->assertStatus(422)->assertJsonValidationErrors(['name' => 'You are not in a grocery group.']);
    }

    public function test_a_post_creates_the_recipe_with_its_ingredients_and_stamps_the_callers_group(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::COUNT]);

        $response = $this->jsonAs($this->user, 'POST', 'api/recipes', [
            'name' => 'Tacos',
            'description' => 'Weeknight tacos',
            'ingredients' => [
                ['groceryItemId' => $item->id, 'quantity' => 2.5],
            ],
        ])->assertSuccessful()->json();

        self::assertEquals('Tacos', $response['name']);
        self::assertCount(1, $response['ingredients']);
        self::assertEquals($item->id, $response['ingredients'][0]['groceryItemId']);
        self::assertEquals(2.5, $response['ingredients'][0]['quantity']);
        self::assertEquals($this->group->id, Recipe::query()->findOrFail($response['id'])->user_group_id);
    }

    public function test_a_post_naming_another_groups_item_is_a_422_and_writes_no_row(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id]);
        $countBefore = Recipe::query()->count();

        $this->jsonAs($this->user, 'POST', 'api/recipes', [
            'name' => 'Tacos',
            'description' => null,
            'ingredients' => [
                ['groceryItemId' => $otherItem->id, 'quantity' => null],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors(['ingredients' => 'That item is not on your master list.']);

        self::assertEquals($countBefore, Recipe::query()->count());
    }

    public function test_a_post_naming_the_same_item_twice_is_a_422(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);

        $this->jsonAs($this->user, 'POST', 'api/recipes', [
            'name' => 'Tacos',
            'description' => null,
            'ingredients' => [
                ['groceryItemId' => $item->id, 'quantity' => null],
                ['groceryItemId' => $item->id, 'quantity' => null],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors(['ingredients' => 'That item is already in this recipe.']);
    }

    public function test_a_post_whose_name_matches_an_existing_recipe_in_another_case_is_a_422(): void
    {
        Recipe::factory()->create(['user_group_id' => $this->group->id, 'name' => 'Tacos']);

        $this->jsonAs($this->user, 'POST', 'api/recipes', [
            'name' => 'TACOS',
            'description' => null,
            'ingredients' => [],
        ])->assertStatus(422)->assertJsonValidationErrors(['name' => 'That recipe already exists.']);
    }

    public function test_a_post_with_a_quantity_of_zero_is_a_422(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::COUNT]);

        $this->jsonAs($this->user, 'POST', 'api/recipes', [
            'name' => 'Tacos',
            'description' => null,
            'ingredients' => [
                ['groceryItemId' => $item->id, 'quantity' => 0],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors('ingredients.0.quantity');
    }

    public function test_a_post_with_a_quantity_on_a_none_unit_item_stores_null_for_it(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);

        $response = $this->jsonAs($this->user, 'POST', 'api/recipes', [
            'name' => 'Tacos',
            'description' => null,
            'ingredients' => [
                ['groceryItemId' => $item->id, 'quantity' => 3],
            ],
        ])->assertSuccessful()->json();

        self::assertNull($response['ingredients'][0]['quantity']);
    }

    public function test_a_put_replaces_the_ingredient_list(): void
    {
        $keptItem = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::COUNT]);
        $droppedItem = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::COUNT]);
        $newItem = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::COUNT]);

        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id, 'name' => 'Tacos']);
        RecipeItem::factory()->create(['recipe_id' => $recipe->id, 'grocery_item_id' => $keptItem->id, 'quantity' => 1]);
        RecipeItem::factory()->create(['recipe_id' => $recipe->id, 'grocery_item_id' => $droppedItem->id, 'quantity' => 1]);

        $response = $this->jsonAs($this->user, 'PUT', "api/recipes/{$recipe->id}", [
            'name' => 'Tacos',
            'description' => null,
            'ingredients' => [
                ['groceryItemId' => $keptItem->id, 'quantity' => 5],
                ['groceryItemId' => $newItem->id, 'quantity' => 2],
            ],
        ])->assertSuccessful()->json();

        $ingredients = collect($response['ingredients'])->keyBy('groceryItemId');
        self::assertCount(2, $ingredients);
        self::assertEquals(5, $ingredients[$keptItem->id]['quantity']);
        self::assertEquals(2, $ingredients[$newItem->id]['quantity']);
        self::assertFalse($ingredients->has($droppedItem->id));
    }

    public function test_a_put_keeping_the_recipes_own_name_is_not_a_422(): void
    {
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id, 'name' => 'Tacos']);

        $this->jsonAs($this->user, 'PUT', "api/recipes/{$recipe->id}", [
            'name' => 'Tacos',
            'description' => null,
            'ingredients' => [],
        ])->assertSuccessful();
    }

    public function test_a_put_naming_another_groups_item_is_a_422_and_leaves_ingredients_in_place(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::COUNT]);
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id]);

        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id, 'name' => 'Tacos']);
        RecipeItem::factory()->create(['recipe_id' => $recipe->id, 'grocery_item_id' => $item->id, 'quantity' => 1]);

        $this->jsonAs($this->user, 'PUT', "api/recipes/{$recipe->id}", [
            'name' => 'Tacos',
            'description' => null,
            'ingredients' => [
                ['groceryItemId' => $otherItem->id, 'quantity' => null],
            ],
        ])->assertStatus(422)->assertJsonValidationErrors(['ingredients' => 'That item is not on your master list.']);

        $remaining = $recipe->ingredients()->get();
        self::assertCount(1, $remaining);
        self::assertEquals($item->id, $remaining->first()->grocery_item_id);
    }

    public function test_a_put_against_another_groups_recipe_is_a_401_and_changes_nothing(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherRecipe = Recipe::factory()->create(['user_group_id' => $otherGroup->id, 'name' => 'Chili']);

        $this->jsonAs($this->user, 'PUT', "api/recipes/{$otherRecipe->id}", [
            'name' => 'Something Else',
            'description' => null,
            'ingredients' => [],
        ])->assertUnauthorized();

        self::assertEquals('Chili', $otherRecipe->fresh()->name);
    }

    public function test_a_delete_removes_the_recipe_and_its_ingredients_and_against_another_groups_recipe_is_a_401(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id]);
        $recipeItem = RecipeItem::factory()->create(['recipe_id' => $recipe->id, 'grocery_item_id' => $item->id]);

        $this->jsonAs($this->user, 'DELETE', "api/recipes/{$recipe->id}")
            ->assertSuccessful();

        self::assertNull($recipe->fresh());
        self::assertNull($recipeItem->fresh());

        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherRecipe = Recipe::factory()->create(['user_group_id' => $otherGroup->id]);

        $this->jsonAs($this->user, 'DELETE', "api/recipes/{$otherRecipe->id}")
            ->assertUnauthorized();

        self::assertNotNull($otherRecipe->fresh());
    }

    private function jsonAs(User $user, string $method, string $uri, array $data = []): TestResponse
    {
        $token = auth()->login($user);

        return $this->actingAs($user)->json($method, $uri, $data, [
            'accept' => 'application/json',
            'authorization' => 'Bearer '.$token,
        ]);
    }
}
