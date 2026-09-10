<?php

namespace Tests\Feature\Grocery;

use App\Enums\Roles;
use App\Models\Grocery\GroceryCategory;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryListItem;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GroceryItemsTest extends TestCase
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

    public function test_the_index_returns_the_callers_groups_items(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);

        $names = collect($this->jsonAs($this->user, 'GET', 'api/grocery-items')
            ->assertSuccessful()
            ->json())->pluck('name');

        self::assertTrue($names->contains($item->name));
    }

    public function test_the_index_excludes_another_grocery_groups_items(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id, 'name' => 'stranger item']);

        $names = collect($this->jsonAs($this->user, 'GET', 'api/grocery-items')
            ->assertSuccessful()
            ->json())->pluck('name');

        self::assertFalse($names->contains($otherItem->name));
    }

    public function test_the_index_is_empty_for_a_caller_with_no_grocery_group(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);

        $this->jsonAs($groupless, 'GET', 'api/grocery-items')
            ->assertSuccessful()
            ->assertJson([]);
    }

    public function test_a_post_from_a_caller_with_no_grocery_group_is_a_422(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);

        $this->jsonAs($groupless, 'POST', 'api/grocery-items', [
            'name' => 'milk',
            'notes' => null,
            'tags' => [],
            'unit' => 'none',
        ])->assertStatus(422);
    }

    public function test_a_post_creates_the_item_in_the_callers_group_with_its_tags_attached(): void
    {
        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-items', [
            'name' => 'milk',
            'notes' => 'whole',
            'tags' => ['costco', 'dairy'],
            'unit' => 'weight',
            'defaultQuantity' => 1,
        ])->assertSuccessful()->json();

        self::assertEquals('milk', $response['name']);
        self::assertEquals(['costco', 'dairy'], $response['tags']);
        self::assertEquals('weight', $response['unit']);
        self::assertEquals(1, $response['defaultQuantity']);
        self::assertEquals($this->group->id, GroceryItem::query()->findOrFail($response['id'])->user_group_id);
    }

    public function test_a_put_changes_name_notes_tags_unit_and_default_quantity_detaching_a_removed_tag(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $item->updateTags(['costco', 'produce']);

        $response = $this->jsonAs($this->user, 'PUT', "api/grocery-items/{$item->id}", [
            'name' => 'renamed',
            'notes' => 'updated notes',
            'tags' => ['produce'],
            'unit' => 'count',
            'defaultQuantity' => 4,
        ])->assertSuccessful()->json();

        self::assertEquals('renamed', $response['name']);
        self::assertEquals('updated notes', $response['notes']);
        self::assertEquals(['produce'], $response['tags']);
        self::assertEquals('count', $response['unit']);
        self::assertEquals(4, $response['defaultQuantity']);
    }

    public function test_a_post_carrying_a_category_name_creates_the_category_and_links_the_item(): void
    {
        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-items', [
            'name' => 'milk',
            'notes' => null,
            'tags' => [],
            'unit' => 'none',
            'category' => 'dairy',
        ])->assertSuccessful()->json();

        self::assertEquals('dairy', $response['category']);

        $category = GroceryCategory::query()->where('user_group_id', '=', $this->group->id)->firstOrFail();
        self::assertEquals('dairy', $category->name);
        self::assertEquals($category->id, GroceryItem::query()->findOrFail($response['id'])->grocery_category_id);
    }

    public function test_a_post_naming_an_existing_category_differing_only_in_case_reuses_it(): void
    {
        $category = GroceryCategory::factory()->create(['user_group_id' => $this->group->id, 'name' => 'dairy']);

        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-items', [
            'name' => 'milk',
            'notes' => null,
            'tags' => [],
            'unit' => 'none',
            'category' => 'DAIRY',
        ])->assertSuccessful()->json();

        self::assertEquals($category->id, GroceryItem::query()->findOrFail($response['id'])->grocery_category_id);
        self::assertEquals(1, GroceryCategory::query()->where('user_group_id', '=', $this->group->id)->count());
    }

    public function test_a_post_with_a_null_empty_or_whitespace_category_leaves_it_uncategorised(): void
    {
        foreach ([null, '', '   '] as $category) {
            $response = $this->jsonAs($this->user, 'POST', 'api/grocery-items', [
                'name' => 'milk '.uniqid(),
                'notes' => null,
                'tags' => [],
                'unit' => 'none',
                'category' => $category,
            ])->assertSuccessful()->json();

            self::assertNull($response['category']);
        }

        self::assertEquals(0, GroceryCategory::query()->where('user_group_id', '=', $this->group->id)->count());
    }

    public function test_a_put_changes_the_items_category_creating_the_new_row_and_leaving_the_old_one_in_place(): void
    {
        $oldCategory = GroceryCategory::factory()->create(['user_group_id' => $this->group->id, 'name' => 'dairy']);
        $item = GroceryItem::factory()->create([
            'user_group_id' => $this->group->id,
            'grocery_category_id' => $oldCategory->id,
        ]);

        $response = $this->jsonAs($this->user, 'PUT', "api/grocery-items/{$item->id}", [
            'name' => $item->name,
            'notes' => null,
            'tags' => [],
            'unit' => 'none',
            'category' => 'produce',
        ])->assertSuccessful()->json();

        self::assertEquals('produce', $response['category']);
        self::assertNotNull($oldCategory->fresh());
    }

    public function test_the_same_category_name_in_a_different_grocery_group_is_a_separate_row(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherCategory = GroceryCategory::factory()->create(['user_group_id' => $otherGroup->id, 'name' => 'dairy']);

        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-items', [
            'name' => 'milk',
            'notes' => null,
            'tags' => [],
            'unit' => 'none',
            'category' => 'dairy',
        ])->assertSuccessful()->json();

        $category = GroceryItem::query()->findOrFail($response['id'])->grocery_category_id;
        self::assertNotEquals($otherCategory->id, $category);
    }

    public function test_a_post_with_an_unknown_unit_is_a_422(): void
    {
        $this->jsonAs($this->user, 'POST', 'api/grocery-items', [
            'name' => 'milk',
            'notes' => null,
            'tags' => [],
            'unit' => 'gallons',
        ])->assertStatus(422);
    }

    public function test_a_post_with_no_unit_and_a_default_quantity_is_a_422(): void
    {
        $this->jsonAs($this->user, 'POST', 'api/grocery-items', [
            'name' => 'salt',
            'notes' => null,
            'tags' => [],
            'unit' => 'none',
            'defaultQuantity' => 1,
        ])->assertStatus(422);
    }

    public function test_a_post_whose_name_matches_an_existing_item_differing_only_in_case_is_a_422(): void
    {
        GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'name' => 'Milk']);

        $this->jsonAs($this->user, 'POST', 'api/grocery-items', [
            'name' => 'MILK',
            'notes' => null,
            'tags' => [],
            'unit' => 'none',
        ])->assertStatus(422);
    }

    public function test_the_same_name_in_a_different_group_succeeds(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        GroceryItem::factory()->create(['user_group_id' => $otherGroup->id, 'name' => 'Milk']);

        $this->jsonAs($this->user, 'POST', 'api/grocery-items', [
            'name' => 'Milk',
            'notes' => null,
            'tags' => [],
            'unit' => 'none',
        ])->assertSuccessful();
    }

    public function test_a_put_against_another_groups_item_is_a_401_and_leaves_the_row_unchanged(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id, 'name' => 'untouched']);

        $this->jsonAs($this->user, 'PUT', "api/grocery-items/{$otherItem->id}", [
            'name' => 'hijacked',
            'notes' => null,
            'tags' => [],
            'unit' => 'none',
        ])->assertUnauthorized();

        self::assertEquals('untouched', $otherItem->fresh()->name);
    }

    public function test_a_delete_against_another_groups_item_is_a_401_and_leaves_the_row_present(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id]);

        $this->jsonAs($this->user, 'DELETE', "api/grocery-items/{$otherItem->id}")
            ->assertUnauthorized();

        self::assertNotNull($otherItem->fresh());
    }

    public function test_a_delete_of_the_callers_own_item_removes_the_row(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);

        $this->jsonAs($this->user, 'DELETE', "api/grocery-items/{$item->id}")
            ->assertSuccessful();

        self::assertNull($item->fresh());
    }

    public function test_a_delete_removes_its_list_entries_bought_and_unbought_and_leaves_another_items_entries_alone(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $otherItem = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);

        $unboughtEntry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
        ]);
        $boughtEntry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
            'bought_at' => now(),
        ]);
        $otherEntry = GroceryListItem::factory()->create([
            'grocery_item_id' => $otherItem->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
        ]);

        $this->jsonAs($this->user, 'DELETE', "api/grocery-items/{$item->id}")
            ->assertSuccessful();

        self::assertNull($unboughtEntry->fresh());
        self::assertNull($boughtEntry->fresh());
        self::assertNotNull($otherEntry->fresh());
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
