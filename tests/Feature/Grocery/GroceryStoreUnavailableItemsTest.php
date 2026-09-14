<?php

namespace Tests\Feature\Grocery;

use App\Enums\Roles;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryStore;
use App\Models\Grocery\GroceryStoreUnavailableItem;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GroceryStoreUnavailableItemsTest extends TestCase
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

    public function test_the_index_returns_the_callers_groups_rows(): void
    {
        $row = $this->makeUnavailableItem($this->group);

        $ids = collect($this->jsonAs($this->user, 'GET', 'api/grocery-store-unavailable-items')
            ->assertSuccessful()
            ->json())->pluck('id');

        self::assertTrue($ids->contains($row->id));
    }

    public function test_the_index_excludes_another_grocery_groups_rows(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherRow = $this->makeUnavailableItem($otherGroup);

        $ids = collect($this->jsonAs($this->user, 'GET', 'api/grocery-store-unavailable-items')
            ->assertSuccessful()
            ->json())->pluck('id');

        self::assertFalse($ids->contains($otherRow->id));
    }

    public function test_the_index_is_empty_for_a_caller_with_no_grocery_group(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);

        $this->jsonAs($groupless, 'GET', 'api/grocery-store-unavailable-items')
            ->assertSuccessful()
            ->assertJson([]);
    }

    public function test_a_post_from_a_caller_with_no_grocery_group_is_a_422(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);
        $store = GroceryStore::factory()->create(['user_group_id' => $this->group->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);

        $this->jsonAs($groupless, 'POST', 'api/grocery-store-unavailable-items', [
            'groceryStoreId' => $store->id,
            'groceryItemId' => $item->id,
        ])->assertStatus(422);
    }

    public function test_a_post_marks_the_item_unavailable_at_the_store_and_stamps_the_callers_group(): void
    {
        $store = GroceryStore::factory()->create(['user_group_id' => $this->group->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);

        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-store-unavailable-items', [
            'groceryStoreId' => $store->id,
            'groceryItemId' => $item->id,
        ])->assertSuccessful()->json();

        self::assertEquals($store->id, $response['groceryStoreId']);
        self::assertEquals($item->id, $response['groceryItemId']);
        self::assertEquals($this->group->id, GroceryStoreUnavailableItem::query()->findOrFail($response['id'])->user_group_id);
    }

    public function test_a_post_naming_another_groups_store_is_a_422(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherStore = GroceryStore::factory()->create(['user_group_id' => $otherGroup->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);

        $this->jsonAs($this->user, 'POST', 'api/grocery-store-unavailable-items', [
            'groceryStoreId' => $otherStore->id,
            'groceryItemId' => $item->id,
        ])->assertStatus(422);
    }

    public function test_a_post_naming_another_groups_item_is_a_422(): void
    {
        $store = GroceryStore::factory()->create(['user_group_id' => $this->group->id]);
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id]);

        $this->jsonAs($this->user, 'POST', 'api/grocery-store-unavailable-items', [
            'groceryStoreId' => $store->id,
            'groceryItemId' => $otherItem->id,
        ])->assertStatus(422);
    }

    public function test_a_post_for_a_store_and_item_that_is_already_marked_is_a_422(): void
    {
        $store = GroceryStore::factory()->create(['user_group_id' => $this->group->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        GroceryStoreUnavailableItem::factory()->create([
            'user_group_id' => $this->group->id,
            'grocery_store_id' => $store->id,
            'grocery_item_id' => $item->id,
        ]);

        $this->jsonAs($this->user, 'POST', 'api/grocery-store-unavailable-items', [
            'groceryStoreId' => $store->id,
            'groceryItemId' => $item->id,
        ])->assertStatus(422);
    }

    public function test_a_delete_removes_the_row_and_against_another_groups_row_is_a_401_leaving_it_present(): void
    {
        $row = $this->makeUnavailableItem($this->group);

        $this->jsonAs($this->user, 'DELETE', "api/grocery-store-unavailable-items/{$row->id}")
            ->assertSuccessful();

        self::assertNull($row->fresh());

        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherRow = $this->makeUnavailableItem($otherGroup);

        $this->jsonAs($this->user, 'DELETE', "api/grocery-store-unavailable-items/{$otherRow->id}")
            ->assertUnauthorized();

        self::assertNotNull($otherRow->fresh());
    }

    private function makeUnavailableItem(UserGroup $group): GroceryStoreUnavailableItem
    {
        $store = GroceryStore::factory()->create(['user_group_id' => $group->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $group->id]);

        return GroceryStoreUnavailableItem::factory()->create([
            'user_group_id' => $group->id,
            'grocery_store_id' => $store->id,
            'grocery_item_id' => $item->id,
        ]);
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
