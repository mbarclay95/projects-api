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

class GroceryListItemBatchTest extends TestCase
{
    private const URI = 'api/grocery-list-items/batch';

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

    public function test_an_item_not_on_the_list_becomes_an_entry_with_the_sent_quantity(): void
    {
        $item = $this->item(GroceryItemUnit::COUNT);

        $this->postBatch($this->user, [['groceryItemId' => $item->id, 'quantity' => 2]])
            ->assertSuccessful()
            ->assertExactJson(['added' => 1, 'alreadyOnList' => 0]);

        $entry = GroceryListItem::query()->where('grocery_item_id', '=', $item->id)->firstOrFail();
        self::assertEquals(2, $entry->quantity);
        self::assertEquals($this->user->id, $entry->added_by_user_id);
        self::assertEquals($this->group->id, $entry->user_group_id);
    }

    public function test_a_null_quantity_gets_the_items_default_quantity(): void
    {
        $item = $this->item(GroceryItemUnit::COUNT, ['default_quantity' => 3]);

        $this->postBatch($this->user, [['groceryItemId' => $item->id, 'quantity' => null]])->assertSuccessful();

        $entry = GroceryListItem::query()->where('grocery_item_id', '=', $item->id)->firstOrFail();
        self::assertEquals(3, $entry->quantity);
    }

    public function test_a_none_unit_item_becomes_an_entry_with_a_null_quantity(): void
    {
        $item = $this->item(GroceryItemUnit::NONE);

        $this->postBatch($this->user, [['groceryItemId' => $item->id, 'quantity' => 2]])->assertSuccessful();

        $entry = GroceryListItem::query()->where('grocery_item_id', '=', $item->id)->firstOrFail();
        self::assertNull($entry->quantity);
    }

    public function test_an_item_already_on_the_list_has_its_quantity_raised_and_no_second_entry(): void
    {
        $item = $this->item(GroceryItemUnit::COUNT);
        $otherUser = User::factory()->create();
        $entry = $this->entry($item, ['added_by_user_id' => $otherUser->id, 'quantity' => 1]);

        $this->postBatch($this->user, [['groceryItemId' => $item->id, 'quantity' => 2]])
            ->assertSuccessful()
            ->assertExactJson(['added' => 1, 'alreadyOnList' => 0]);

        self::assertEquals(1, GroceryListItem::query()->count());
        $entry->refresh();
        self::assertEquals(3, $entry->quantity);
        self::assertEquals($otherUser->id, $entry->added_by_user_id);
    }

    public function test_an_existing_entry_with_a_null_quantity_ends_up_with_the_sent_amount(): void
    {
        $item = $this->item(GroceryItemUnit::COUNT);
        $entry = $this->entry($item, ['quantity' => null]);

        $this->postBatch($this->user, [['groceryItemId' => $item->id, 'quantity' => 2]])->assertSuccessful();

        self::assertEquals(2, $entry->refresh()->quantity);
    }

    public function test_a_none_unit_item_already_on_the_list_is_left_unchanged_and_counted(): void
    {
        $item = $this->item(GroceryItemUnit::NONE);
        $entry = $this->entry($item, ['quantity' => null]);

        $this->postBatch($this->user, [['groceryItemId' => $item->id, 'quantity' => 2]])
            ->assertSuccessful()
            ->assertExactJson(['added' => 0, 'alreadyOnList' => 1]);

        self::assertNull($entry->refresh()->quantity);
        self::assertEquals(1, GroceryListItem::query()->count());
    }

    public function test_an_item_whose_only_entry_is_bought_gets_a_new_entry(): void
    {
        $item = $this->item(GroceryItemUnit::COUNT);
        $bought = $this->entry($item, ['quantity' => 1, 'bought_at' => now()]);

        $this->postBatch($this->user, [['groceryItemId' => $item->id, 'quantity' => 2]])
            ->assertSuccessful()
            ->assertExactJson(['added' => 1, 'alreadyOnList' => 0]);

        self::assertEquals(2, GroceryListItem::query()->count());
        $bought->refresh();
        self::assertEquals(1, $bought->quantity);
        self::assertNotNull($bought->bought_at);
    }

    public function test_a_batch_counts_new_and_raised_entries_as_added_and_unchanged_ones_as_already_on_the_list(): void
    {
        $new = $this->item(GroceryItemUnit::COUNT);
        $raised = $this->item(GroceryItemUnit::COUNT);
        $unchanged = $this->item(GroceryItemUnit::NONE);
        $this->entry($raised, ['quantity' => 1]);
        $this->entry($unchanged);

        $this->postBatch($this->user, [
            ['groceryItemId' => $new->id, 'quantity' => 1],
            ['groceryItemId' => $raised->id, 'quantity' => 1],
            ['groceryItemId' => $unchanged->id, 'quantity' => null],
        ])
            ->assertSuccessful()
            ->assertExactJson(['added' => 2, 'alreadyOnList' => 1]);
    }

    public function test_an_item_from_another_groups_master_list_is_a_422(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $foreign = GroceryItem::factory()->create(['user_group_id' => $otherGroup->id, 'unit' => GroceryItemUnit::COUNT]);
        $own = $this->item(GroceryItemUnit::COUNT);

        $this->postBatch($this->user, [
            ['groceryItemId' => $own->id, 'quantity' => 1],
            ['groceryItemId' => $foreign->id, 'quantity' => 1],
        ])->assertJsonValidationErrors('items');

        self::assertEquals(0, GroceryListItem::query()->count());
    }

    public function test_a_caller_with_no_grocery_group_is_a_422(): void
    {
        $loner = User::factory()->create();
        $loner->assignRole(Roles::GROCERY_ROLE);
        $item = $this->item(GroceryItemUnit::COUNT);

        $this->postBatch($loner, [['groceryItemId' => $item->id, 'quantity' => 1]])
            ->assertJsonValidationErrors('items');

        self::assertEquals(0, GroceryListItem::query()->count());
    }

    public function test_the_same_item_twice_is_a_422(): void
    {
        $item = $this->item(GroceryItemUnit::COUNT);

        $this->postBatch($this->user, [
            ['groceryItemId' => $item->id, 'quantity' => 1],
            ['groceryItemId' => $item->id, 'quantity' => 1],
        ])->assertJsonValidationErrors(['items.0.groceryItemId', 'items.1.groceryItemId']);

        self::assertEquals(0, GroceryListItem::query()->count());
    }

    public function test_a_quantity_of_zero_is_a_422(): void
    {
        $item = $this->item(GroceryItemUnit::COUNT);

        $this->postBatch($this->user, [['groceryItemId' => $item->id, 'quantity' => 0]])
            ->assertJsonValidationErrors('items.0.quantity');

        self::assertEquals(0, GroceryListItem::query()->count());
    }

    private function item(GroceryItemUnit $unit, array $attributes = []): GroceryItem
    {
        return GroceryItem::factory()->create(['user_group_id' => $this->group->id, 'unit' => $unit] + $attributes);
    }

    private function entry(GroceryItem $item, array $attributes = []): GroceryListItem
    {
        return GroceryListItem::factory()->create(array_merge([
            'grocery_item_id' => $item->id,
            'user_group_id' => $this->group->id,
            'added_by_user_id' => $this->user->id,
        ], $attributes));
    }

    private function postBatch(User $user, array $items): TestResponse
    {
        return $this->jsonAs($user, 'POST', self::URI, ['items' => $items]);
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
