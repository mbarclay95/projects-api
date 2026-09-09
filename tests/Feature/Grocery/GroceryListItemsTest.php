<?php

namespace Tests\Feature\Grocery;

use App\Enums\GroceryItemUnit;
use App\Enums\Roles;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryListItem;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GroceryListItemsTest extends TestCase
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

    public function test_the_index_returns_the_callers_groups_entries_with_the_nested_item_and_its_tags(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $item->updateTags(['costco']);
        GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
        ]);

        $response = $this->jsonAs($this->user, 'GET', 'api/grocery-list-items')
            ->assertSuccessful()
            ->json();

        self::assertCount(1, $response);
        self::assertEquals($item->name, $response[0]['groceryItem']['name']);
        self::assertEquals(['costco'], $response[0]['groceryItem']['tags']);
    }

    public function test_the_index_excludes_another_grocery_groups_entries(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id]);
        $otherUser = User::factory()->create();
        GroceryListItem::factory()->create([
            'grocery_item_id' => $otherItem->id,
            'user_group_id' => $otherGroup->id,
            'added_by_user_id' => $otherUser->id,
        ]);

        $this->jsonAs($this->user, 'GET', 'api/grocery-list-items')
            ->assertSuccessful()
            ->assertJson([]);
    }

    public function test_the_index_excludes_bought_entries(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
            'bought_at' => now(),
        ]);

        $this->jsonAs($this->user, 'GET', 'api/grocery-list-items')
            ->assertSuccessful()
            ->assertJson([]);
    }

    public function test_the_index_is_empty_for_a_caller_with_no_grocery_group(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);

        $this->jsonAs($groupless, 'GET', 'api/grocery-list-items')
            ->assertSuccessful()
            ->assertJson([]);
    }

    public function test_a_post_from_a_caller_with_no_grocery_group_is_a_422(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);

        $this->jsonAs($groupless, 'POST', 'api/grocery-list-items', [
            'groceryItemId' => $item->id,
        ])->assertStatus(422);
    }

    public function test_a_post_adds_the_item_to_the_callers_list_and_records_added_by_user_id(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);

        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-list-items', [
            'groceryItemId' => $item->id,
        ])->assertSuccessful()->json();

        self::assertEquals($item->id, $response['groceryItemId']);
        self::assertEquals(
            $this->user->id,
            GroceryListItem::query()->findOrFail($response['id'])->added_by_user_id
        );
    }

    public function test_a_post_naming_an_item_in_another_group_is_a_422(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id]);

        $this->jsonAs($this->user, 'POST', 'api/grocery-list-items', [
            'groceryItemId' => $otherItem->id,
        ])->assertStatus(422);
    }

    public function test_a_post_for_an_item_that_already_has_an_unbought_entry_is_a_422(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
        ]);

        $this->jsonAs($this->user, 'POST', 'api/grocery-list-items', [
            'groceryItemId' => $item->id,
        ])->assertStatus(422);
    }

    public function test_a_post_for_an_item_whose_only_entry_is_bought_succeeds(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
            'bought_at' => now(),
        ]);

        $this->jsonAs($this->user, 'POST', 'api/grocery-list-items', [
            'groceryItemId' => $item->id,
        ])->assertSuccessful();
    }

    public function test_a_post_with_a_quantity_for_a_none_unit_item_stores_null_and_succeeds(): void
    {
        $item = GroceryItem::factory()->create([
            'user_group_id' => $this->group->id,
            'unit' => GroceryItemUnit::NONE,
        ]);

        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-list-items', [
            'groceryItemId' => $item->id,
            'quantity' => 3,
        ])->assertSuccessful()->json();

        self::assertNull($response['quantity']);
    }

    public function test_a_put_changes_the_quantity(): void
    {
        $item = GroceryItem::factory()->create([
            'user_group_id' => $this->group->id,
            'unit' => GroceryItemUnit::COUNT,
        ]);
        $entry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
        ]);

        $response = $this->jsonAs($this->user, 'PUT', "api/grocery-list-items/{$entry->id}", [
            'quantity' => 5,
            'bought' => false,
        ])->assertSuccessful()->json();

        self::assertEquals(5, $response['quantity']);
    }

    public function test_a_put_with_bought_true_sets_bought_at_and_the_entry_leaves_the_index(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $entry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
        ]);

        $this->jsonAs($this->user, 'PUT', "api/grocery-list-items/{$entry->id}", [
            'quantity' => null,
            'bought' => true,
        ])->assertSuccessful();

        self::assertNotNull($entry->fresh()->bought_at);

        $this->jsonAs($this->user, 'GET', 'api/grocery-list-items')
            ->assertSuccessful()
            ->assertJson([]);
    }

    public function test_a_put_with_a_quantity_against_a_none_unit_item_succeeds(): void
    {
        $item = GroceryItem::factory()->create([
            'user_group_id' => $this->group->id,
            'unit' => GroceryItemUnit::NONE,
        ]);
        $entry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
        ]);

        $response = $this->jsonAs($this->user, 'PUT', "api/grocery-list-items/{$entry->id}", [
            'quantity' => 5,
            'bought' => true,
        ])->assertSuccessful()->json();

        self::assertNull($response['quantity']);
        self::assertTrue($response['bought']);
    }

    public function test_a_put_against_another_groups_entry_is_a_401_and_leaves_the_row_unchanged(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id]);
        $otherUser = User::factory()->create();
        $otherEntry = GroceryListItem::factory()->create([
            'grocery_item_id' => $otherItem->id,
            'user_group_id' => $otherGroup->id,
            'added_by_user_id' => $otherUser->id,
        ]);

        $this->jsonAs($this->user, 'PUT', "api/grocery-list-items/{$otherEntry->id}", [
            'quantity' => 9,
            'bought' => true,
        ])->assertUnauthorized();

        self::assertNull($otherEntry->fresh()->bought_at);
    }

    public function test_a_delete_of_the_callers_own_entry_removes_the_row_and_against_another_groups_entry_is_a_401(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $entry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
        ]);

        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id]);
        $otherUser = User::factory()->create();
        $otherEntry = GroceryListItem::factory()->create([
            'grocery_item_id' => $otherItem->id,
            'user_group_id' => $otherGroup->id,
            'added_by_user_id' => $otherUser->id,
        ]);

        $this->jsonAs($this->user, 'DELETE', "api/grocery-list-items/{$otherEntry->id}")
            ->assertUnauthorized();
        self::assertNotNull($otherEntry->fresh());

        $this->jsonAs($this->user, 'DELETE', "api/grocery-list-items/{$entry->id}")
            ->assertSuccessful();
        self::assertNull($entry->fresh());
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
