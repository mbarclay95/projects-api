<?php

namespace Tests\Feature\Grocery;

use App\Enums\Roles;
use App\Models\Grocery\GroceryCategory;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryStore;
use App\Models\Grocery\GroceryStoreItemCategory;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GroceryStoreItemCategoriesTest extends TestCase
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

    public function test_the_index_returns_the_callers_groups_exceptions(): void
    {
        $exception = $this->makeException($this->group);

        $ids = collect($this->jsonAs($this->user, 'GET', 'api/grocery-store-item-categories')
            ->assertSuccessful()
            ->json())->pluck('id');

        self::assertTrue($ids->contains($exception->id));
    }

    public function test_the_index_excludes_another_grocery_groups_exceptions(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherException = $this->makeException($otherGroup);

        $ids = collect($this->jsonAs($this->user, 'GET', 'api/grocery-store-item-categories')
            ->assertSuccessful()
            ->json())->pluck('id');

        self::assertFalse($ids->contains($otherException->id));
    }

    public function test_the_index_is_empty_for_a_caller_with_no_grocery_group(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);

        $this->jsonAs($groupless, 'GET', 'api/grocery-store-item-categories')
            ->assertSuccessful()
            ->assertJson([]);
    }

    public function test_a_post_creates_an_exception_for_a_store_item_and_category_in_the_callers_group(): void
    {
        $store = GroceryStore::factory()->create(['user_group_id' => $this->group->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $category = GroceryCategory::factory()->create(['user_group_id' => $this->group->id]);

        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-store-item-categories', [
            'groceryStoreId' => $store->id,
            'groceryItemId' => $item->id,
            'groceryCategoryId' => $category->id,
        ])->assertSuccessful()->json();

        self::assertEquals($store->id, $response['groceryStoreId']);
        self::assertEquals($item->id, $response['groceryItemId']);
        self::assertEquals($category->id, $response['groceryCategoryId']);
        self::assertEquals($this->group->id, GroceryStoreItemCategory::query()->findOrFail($response['id'])->user_group_id);
    }

    public function test_a_post_naming_another_groups_store_is_a_422(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherStore = GroceryStore::factory()->create(['user_group_id' => $otherGroup->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $category = GroceryCategory::factory()->create(['user_group_id' => $this->group->id]);

        $this->jsonAs($this->user, 'POST', 'api/grocery-store-item-categories', [
            'groceryStoreId' => $otherStore->id,
            'groceryItemId' => $item->id,
            'groceryCategoryId' => $category->id,
        ])->assertStatus(422);
    }

    public function test_a_post_naming_another_groups_item_is_a_422(): void
    {
        $store = GroceryStore::factory()->create(['user_group_id' => $this->group->id]);
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id]);
        $category = GroceryCategory::factory()->create(['user_group_id' => $this->group->id]);

        $this->jsonAs($this->user, 'POST', 'api/grocery-store-item-categories', [
            'groceryStoreId' => $store->id,
            'groceryItemId' => $otherItem->id,
            'groceryCategoryId' => $category->id,
        ])->assertStatus(422);
    }

    public function test_a_post_naming_another_groups_category_is_a_422(): void
    {
        $store = GroceryStore::factory()->create(['user_group_id' => $this->group->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherCategory = GroceryCategory::factory()->create(['user_group_id' => $otherGroup->id]);

        $this->jsonAs($this->user, 'POST', 'api/grocery-store-item-categories', [
            'groceryStoreId' => $store->id,
            'groceryItemId' => $item->id,
            'groceryCategoryId' => $otherCategory->id,
        ])->assertStatus(422);
    }

    public function test_a_post_for_a_store_and_item_that_already_has_an_exception_is_a_422(): void
    {
        $store = GroceryStore::factory()->create(['user_group_id' => $this->group->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $category = GroceryCategory::factory()->create(['user_group_id' => $this->group->id]);
        GroceryStoreItemCategory::factory()->create([
            'user_group_id' => $this->group->id,
            'grocery_store_id' => $store->id,
            'grocery_item_id' => $item->id,
            'grocery_category_id' => $category->id,
        ]);

        $otherCategory = GroceryCategory::factory()->create(['user_group_id' => $this->group->id]);

        $this->jsonAs($this->user, 'POST', 'api/grocery-store-item-categories', [
            'groceryStoreId' => $store->id,
            'groceryItemId' => $item->id,
            'groceryCategoryId' => $otherCategory->id,
        ])->assertStatus(422);
    }

    public function test_a_put_changes_the_exceptions_category_and_against_another_groups_exception_is_a_401_and_leaves_it_unchanged(): void
    {
        $exception = $this->makeException($this->group);
        $newCategory = GroceryCategory::factory()->create(['user_group_id' => $this->group->id]);

        $response = $this->jsonAs($this->user, 'PUT', "api/grocery-store-item-categories/{$exception->id}", [
            'groceryCategoryId' => $newCategory->id,
        ])->assertSuccessful()->json();

        self::assertEquals($newCategory->id, $response['groceryCategoryId']);

        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherException = $this->makeException($otherGroup);

        $this->jsonAs($this->user, 'PUT', "api/grocery-store-item-categories/{$otherException->id}", [
            'groceryCategoryId' => $newCategory->id,
        ])->assertUnauthorized();

        self::assertEquals($otherException->grocery_category_id, $otherException->fresh()->grocery_category_id);
    }

    public function test_a_delete_removes_the_row_and_against_another_groups_exception_is_a_401_leaving_it_present(): void
    {
        $exception = $this->makeException($this->group);

        $this->jsonAs($this->user, 'DELETE', "api/grocery-store-item-categories/{$exception->id}")
            ->assertSuccessful();

        self::assertNull($exception->fresh());

        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherException = $this->makeException($otherGroup);

        $this->jsonAs($this->user, 'DELETE', "api/grocery-store-item-categories/{$otherException->id}")
            ->assertUnauthorized();

        self::assertNotNull($otherException->fresh());
    }

    private function makeException(UserGroup $group): GroceryStoreItemCategory
    {
        $store = GroceryStore::factory()->create(['user_group_id' => $group->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $group->id]);
        $category = GroceryCategory::factory()->create(['user_group_id' => $group->id]);

        return GroceryStoreItemCategory::factory()->create([
            'user_group_id' => $group->id,
            'grocery_store_id' => $store->id,
            'grocery_item_id' => $item->id,
            'grocery_category_id' => $category->id,
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
