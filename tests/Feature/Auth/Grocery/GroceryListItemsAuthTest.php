<?php

namespace Tests\Feature\Auth\Grocery;

use App\Enums\Roles;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryListItem;
use App\Models\UserGroups\UserGroup;
use Tests\Feature\Auth\AuthTestCase;

class GroceryListItemsAuthTest extends AuthTestCase
{
    /**
     * INDEX
     */
    public function test_get_grocery_list_items_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsGET('api/grocery-list-items');
    }

    /**
     * STORE
     */
    public function test_post_grocery_list_item_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsPOST('api/grocery-list-items');
    }

    /**
     * UPDATE
     */
    public function test_put_grocery_list_item_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $group = UserGroup::factory()->grocery()->create();
        $group->members()->attach($this->goodUser->id, ['scope' => 'grocery']);
        /** @var GroceryItem $item */
        $item = GroceryItem::factory()->create(['user_group_id' => $group->id]);
        $entry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $group->id,
            'added_by_user_id' => $this->goodUser->id,
        ]);
        $this->runTestsPUT("api/grocery-list-items/{$entry->id}");
    }

    /**
     * DESTROY
     */
    public function test_delete_grocery_list_item_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $group = UserGroup::factory()->grocery()->create();
        $group->members()->attach($this->goodUser->id, ['scope' => 'grocery']);
        /** @var GroceryItem $item */
        $item = GroceryItem::factory()->create(['user_group_id' => $group->id]);
        $entry = GroceryListItem::factory()->create([
            'grocery_item_id' => $item->id,
            'user_group_id' => $group->id,
            'added_by_user_id' => $this->goodUser->id,
        ]);
        $this->runTestsDELETE("api/grocery-list-items/{$entry->id}");
    }
}
