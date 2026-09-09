<?php

namespace Tests\Feature\Grocery;

use App\Enums\Roles;
use App\Models\Grocery\GroceryItem;
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
        ])->assertStatus(422);
    }

    public function test_a_post_creates_the_item_in_the_callers_group_with_its_tags_attached(): void
    {
        $response = $this->jsonAs($this->user, 'POST', 'api/grocery-items', [
            'name' => 'milk',
            'notes' => 'whole',
            'tags' => ['costco', 'dairy'],
        ])->assertSuccessful()->json();

        self::assertEquals('milk', $response['name']);
        self::assertEquals(['costco', 'dairy'], $response['tags']);
        self::assertEquals($this->group->id, GroceryItem::query()->findOrFail($response['id'])->user_group_id);
    }

    public function test_a_put_changes_name_notes_and_tags_detaching_a_removed_tag(): void
    {
        $item = GroceryItem::factory()->create(['user_group_id' => $this->group->id]);
        $item->updateTags(['costco', 'produce']);

        $response = $this->jsonAs($this->user, 'PUT', "api/grocery-items/{$item->id}", [
            'name' => 'renamed',
            'notes' => 'updated notes',
            'tags' => ['produce'],
        ])->assertSuccessful()->json();

        self::assertEquals('renamed', $response['name']);
        self::assertEquals('updated notes', $response['notes']);
        self::assertEquals(['produce'], $response['tags']);
    }

    public function test_a_post_whose_name_matches_an_existing_item_differing_only_in_case_is_a_422(): void
    {
        GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'name' => 'Milk']);

        $this->jsonAs($this->user, 'POST', 'api/grocery-items', [
            'name' => 'MILK',
            'notes' => null,
            'tags' => [],
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

    private function jsonAs(User $user, string $method, string $uri, array $data = []): TestResponse
    {
        $token = auth()->login($user);

        return $this->actingAs($user)->json($method, $uri, $data, [
            'accept' => 'application/json',
            'authorization' => 'Bearer '.$token,
        ]);
    }
}
