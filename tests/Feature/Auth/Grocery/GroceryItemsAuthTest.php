<?php

namespace Tests\Feature\Auth\Grocery;

use App\Enums\Roles;
use App\Models\Grocery\GroceryItem;
use App\Models\UserGroups\UserGroup;
use Tests\Feature\Auth\AuthTestCase;

class GroceryItemsAuthTest extends AuthTestCase
{
    /**
     * INDEX
     */
    public function test_get_grocery_items_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsGET('api/grocery-items');
    }

    /**
     * STORE
     */
    public function test_post_grocery_item_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsPOST('api/grocery-items');
    }

    /**
     * UPDATE
     */
    public function test_put_grocery_item_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $group = UserGroup::factory()->grocery()->create();
        $group->members()->attach($this->goodUser->id, ['scope' => 'grocery']);
        /** @var GroceryItem $item */
        $item = GroceryItem::factory()->create(['user_group_id' => $group->id]);
        $this->runTestsPUT("api/grocery-items/{$item->id}");
    }

    /**
     * DESTROY
     */
    public function test_delete_grocery_item_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $group = UserGroup::factory()->grocery()->create();
        $group->members()->attach($this->goodUser->id, ['scope' => 'grocery']);
        /** @var GroceryItem $item */
        $item = GroceryItem::factory()->create(['user_group_id' => $group->id]);
        $this->runTestsDELETE("api/grocery-items/{$item->id}");
    }
}
