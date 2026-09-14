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

class GroceryStoresTest extends TestCase
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

    public function test_the_index_returns_the_callers_groups_stores_ordered_by_name(): void
    {
        GroceryStore::factory()->create(['user_group_id' => $this->group->id, 'name' => 'Yokes']);
        GroceryStore::factory()->create(['user_group_id' => $this->group->id, 'name' => "Trader Joe's"]);

        $names = collect($this->jsonAs($this->user, 'GET', 'api/grocery-stores')
            ->assertSuccessful()
            ->json())->pluck('name');

        self::assertEquals(["Trader Joe's", 'Yokes'], $names->all());
    }

    public function test_the_index_excludes_another_grocery_groups_stores(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherStore = GroceryStore::factory()->create(['user_group_id' => $otherGroup->id, 'name' => 'stranger store']);

        $names = collect($this->jsonAs($this->user, 'GET', 'api/grocery-stores')
            ->assertSuccessful()
            ->json())->pluck('name');

        self::assertFalse($names->contains($otherStore->name));
    }

    public function test_the_index_is_empty_for_a_caller_with_no_grocery_group(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);

        $this->jsonAs($groupless, 'GET', 'api/grocery-stores')
            ->assertSuccessful()
            ->assertJson([]);
    }

    public function test_a_post_from_a_caller_with_no_grocery_group_is_a_422(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);

        $this->jsonAs($groupless, 'POST', 'api/grocery-stores', [
            'name' => 'Safeway',
            'categoryOrder' => [],
        ])->assertStatus(422);
    }

    public function test_a_post_creates_the_store_in_the_callers_group_with_its_category_order_intact(): void
    {
        $first = GroceryCategory::factory()->create(['user_group_id' => $this->group->id, 'name' => 'produce']);
        $second = GroceryCategory::factory()->create(['user_group_id' => $this->group->id, 'name' => 'bakery']);

        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-stores', [
            'name' => 'Safeway',
            'categoryOrder' => [$first->id, $second->id],
        ])->assertSuccessful()->json();

        self::assertEquals('Safeway', $response['name']);
        self::assertEquals([$first->id, $second->id], $response['categoryOrder']);
        self::assertEquals($this->group->id, GroceryStore::query()->findOrFail($response['id'])->user_group_id);
    }

    public function test_a_post_whose_name_matches_an_existing_store_differing_only_in_case_is_a_422(): void
    {
        GroceryStore::factory()->create(['user_group_id' => $this->group->id, 'name' => 'Safeway']);

        $this->jsonAs($this->user, 'POST', 'api/grocery-stores', [
            'name' => 'SAFEWAY',
            'categoryOrder' => [],
        ])->assertStatus(422);
    }

    public function test_a_post_whose_category_order_names_another_groups_category_id_drops_that_id_and_succeeds(): void
    {
        $ownCategory = GroceryCategory::factory()->create(['user_group_id' => $this->group->id, 'name' => 'produce']);
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherCategory = GroceryCategory::factory()->create(['user_group_id' => $otherGroup->id, 'name' => 'bakery']);

        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-stores', [
            'name' => 'Safeway',
            'categoryOrder' => [$ownCategory->id, $otherCategory->id],
        ])->assertSuccessful()->json();

        self::assertEquals([$ownCategory->id], $response['categoryOrder']);
    }

    public function test_a_post_whose_category_order_repeats_an_id_stores_it_once_at_its_first_position(): void
    {
        $first = GroceryCategory::factory()->create(['user_group_id' => $this->group->id, 'name' => 'produce']);
        $second = GroceryCategory::factory()->create(['user_group_id' => $this->group->id, 'name' => 'bakery']);

        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-stores', [
            'name' => 'Safeway',
            'categoryOrder' => [$first->id, $second->id, $first->id],
        ])->assertSuccessful()->json();

        self::assertEquals([$first->id, $second->id], $response['categoryOrder']);
    }

    public function test_a_put_changes_the_name_and_reorders_the_categories(): void
    {
        $first = GroceryCategory::factory()->create(['user_group_id' => $this->group->id, 'name' => 'produce']);
        $second = GroceryCategory::factory()->create(['user_group_id' => $this->group->id, 'name' => 'bakery']);
        $store = GroceryStore::factory()->create([
            'user_group_id' => $this->group->id,
            'name' => 'Safeway',
            'category_order' => [$first->id, $second->id],
        ]);

        $response = $this->jsonAs($this->user, 'PUT', "api/grocery-stores/{$store->id}", [
            'name' => 'Safeway Renamed',
            'categoryOrder' => [$second->id, $first->id],
        ])->assertSuccessful()->json();

        self::assertEquals('Safeway Renamed', $response['name']);
        self::assertEquals([$second->id, $first->id], $response['categoryOrder']);
    }

    public function test_a_put_against_another_groups_store_is_a_401_and_leaves_the_row_unchanged(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherStore = GroceryStore::factory()->create(['user_group_id' => $otherGroup->id, 'name' => 'untouched']);

        $this->jsonAs($this->user, 'PUT', "api/grocery-stores/{$otherStore->id}", [
            'name' => 'hijacked',
            'categoryOrder' => [],
        ])->assertUnauthorized();

        self::assertEquals('untouched', $otherStore->fresh()->name);
    }

    public function test_a_delete_of_the_callers_own_store_removes_the_row_and_against_another_groups_store_is_a_401(): void
    {
        $store = GroceryStore::factory()->create(['user_group_id' => $this->group->id]);

        $this->jsonAs($this->user, 'DELETE', "api/grocery-stores/{$store->id}")
            ->assertSuccessful();

        self::assertNull($store->fresh());

        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherStore = GroceryStore::factory()->create(['user_group_id' => $otherGroup->id]);

        $this->jsonAs($this->user, 'DELETE', "api/grocery-stores/{$otherStore->id}")
            ->assertUnauthorized();

        self::assertNotNull($otherStore->fresh());
    }

    public function test_a_delete_removes_its_exception_rows_and_leaves_another_stores_alone(): void
    {
        $store = GroceryStore::factory()->create(['user_group_id' => $this->group->id]);
        $otherStore = GroceryStore::factory()->create(['user_group_id' => $this->group->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $category = GroceryCategory::factory()->create(['user_group_id' => $this->group->id]);

        $exception = GroceryStoreItemCategory::factory()->create([
            'user_group_id' => $this->group->id,
            'grocery_store_id' => $store->id,
            'grocery_item_id' => $item->id,
            'grocery_category_id' => $category->id,
        ]);
        $otherException = GroceryStoreItemCategory::factory()->create([
            'user_group_id' => $this->group->id,
            'grocery_store_id' => $otherStore->id,
            'grocery_item_id' => $item->id,
            'grocery_category_id' => $category->id,
        ]);

        $this->jsonAs($this->user, 'DELETE', "api/grocery-stores/{$store->id}")
            ->assertSuccessful();

        self::assertNull($exception->fresh());
        self::assertNotNull($otherException->fresh());
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
