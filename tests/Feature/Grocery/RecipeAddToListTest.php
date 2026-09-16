<?php

namespace Tests\Feature\Grocery;

use App\Enums\GroceryItemUnit;
use App\Enums\Roles;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryListItem;
use App\Models\Grocery\Recipe;
use App\Models\Grocery\RecipeItem;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RecipeAddToListTest extends TestCase
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

    public function test_an_ingredient_not_on_the_list_becomes_an_entry_with_the_recipes_amount(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::COUNT]);
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id]);
        RecipeItem::factory()->create(['recipe_id' => $recipe->id, 'grocery_item_id' => $item->id, 'quantity' => 2]);

        $this->jsonAs($this->user, 'POST', "api/recipes/{$recipe->id}/add-to-list")
            ->assertSuccessful()
            ->assertJson(['added' => 1, 'alreadyOnList' => 0]);

        $entry = GroceryListItem::query()->where('grocery_item_id', '=', $item->id)->firstOrFail();
        self::assertEquals(2, $entry->quantity);
        self::assertEquals($this->user->id, $entry->added_by_user_id);
        self::assertEquals($this->group->id, $entry->user_group_id);
    }

    public function test_an_ingredient_with_a_null_quantity_gets_the_items_default_quantity(): void
    {
        $item = GroceryItem::factory()->create([
            'user_group_id' => $this->group->id,
            'unit' => GroceryItemUnit::COUNT,
            'default_quantity' => 3,
        ]);
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id]);
        RecipeItem::factory()->create(['recipe_id' => $recipe->id, 'grocery_item_id' => $item->id, 'quantity' => null]);

        $this->jsonAs($this->user, 'POST', "api/recipes/{$recipe->id}/add-to-list")->assertSuccessful();

        $entry = GroceryListItem::query()->where('grocery_item_id', '=', $item->id)->firstOrFail();
        self::assertEquals(3, $entry->quantity);
    }

    public function test_a_none_unit_ingredient_becomes_an_entry_with_a_null_quantity(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::NONE]);
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id]);
        RecipeItem::factory()->create(['recipe_id' => $recipe->id, 'grocery_item_id' => $item->id]);

        $this->jsonAs($this->user, 'POST', "api/recipes/{$recipe->id}/add-to-list")
            ->assertSuccessful()
            ->assertJson(['added' => 1, 'alreadyOnList' => 0]);

        $entry = GroceryListItem::query()->where('grocery_item_id', '=', $item->id)->firstOrFail();
        self::assertNull($entry->quantity);
    }

    public function test_an_ingredient_already_on_the_list_raises_its_quantity_and_creates_no_second_entry(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::COUNT]);
        $otherUser = User::factory()->create();
        $entry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $otherUser->id,
            'quantity' => 1,
        ]);
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id]);
        RecipeItem::factory()->create(['recipe_id' => $recipe->id, 'grocery_item_id' => $item->id, 'quantity' => 2]);

        $this->jsonAs($this->user, 'POST', "api/recipes/{$recipe->id}/add-to-list")
            ->assertSuccessful()
            ->assertJson(['added' => 0, 'alreadyOnList' => 1]);

        self::assertEquals(1, GroceryListItem::query()->where('grocery_item_id', '=', $item->id)->count());
        $entry->refresh();
        self::assertEquals(3, $entry->quantity);
        self::assertEquals($otherUser->id, $entry->added_by_user_id);
    }

    public function test_an_existing_entry_with_a_null_quantity_for_a_count_item_ends_up_with_the_recipes_amount(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::COUNT]);
        $entry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
            'quantity' => null,
        ]);
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id]);
        RecipeItem::factory()->create(['recipe_id' => $recipe->id, 'grocery_item_id' => $item->id, 'quantity' => 2]);

        $this->jsonAs($this->user, 'POST', "api/recipes/{$recipe->id}/add-to-list")->assertSuccessful();

        self::assertEquals(2, $entry->fresh()->quantity);
    }

    public function test_a_none_unit_ingredient_already_on_the_list_leaves_the_entry_unchanged(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::NONE]);
        $entry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
            'quantity' => null,
        ]);
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id]);
        RecipeItem::factory()->create(['recipe_id' => $recipe->id, 'grocery_item_id' => $item->id]);

        $this->jsonAs($this->user, 'POST', "api/recipes/{$recipe->id}/add-to-list")
            ->assertSuccessful()
            ->assertJson(['added' => 0, 'alreadyOnList' => 1]);

        self::assertNull($entry->fresh()->quantity);
    }

    public function test_an_item_whose_only_entry_is_bought_gets_a_new_entry_and_the_bought_entry_is_untouched(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => GroceryItemUnit::COUNT]);
        $boughtEntry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
            'quantity' => 1,
            'bought_at' => now(),
        ]);
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id]);
        RecipeItem::factory()->create(['recipe_id' => $recipe->id, 'grocery_item_id' => $item->id, 'quantity' => 2]);

        $this->jsonAs($this->user, 'POST', "api/recipes/{$recipe->id}/add-to-list")
            ->assertSuccessful()
            ->assertJson(['added' => 1, 'alreadyOnList' => 0]);

        self::assertEquals(1, $boughtEntry->fresh()->quantity);
        self::assertNotNull($boughtEntry->fresh()->bought_at);

        $newEntry = GroceryListItem::query()
            ->where('grocery_item_id', '=', $item->id)
            ->whereNull('bought_at')
            ->firstOrFail();
        self::assertEquals(2, $newEntry->quantity);
    }

    public function test_a_post_against_another_groups_recipe_is_a_401_and_writes_nothing(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $item = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id, 'unit' => GroceryItemUnit::COUNT]);
        $otherRecipe = Recipe::factory()->create(['user_group_id' => $otherGroup->id]);
        RecipeItem::factory()->create(['recipe_id' => $otherRecipe->id, 'grocery_item_id' => $item->id, 'quantity' => 1]);

        $this->jsonAs($this->user, 'POST', "api/recipes/{$otherRecipe->id}/add-to-list")->assertUnauthorized();

        self::assertEquals(0, GroceryListItem::query()->count());
    }

    public function test_a_post_against_a_recipe_id_that_does_not_exist_is_a_404(): void
    {
        $this->jsonAs($this->user, 'POST', 'api/recipes/999999/add-to-list')->assertNotFound();
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
