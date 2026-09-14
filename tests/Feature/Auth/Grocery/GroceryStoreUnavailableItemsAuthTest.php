<?php

namespace Tests\Feature\Auth\Grocery;

use App\Enums\Roles;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryStore;
use App\Models\Grocery\GroceryStoreUnavailableItem;
use App\Models\UserGroups\UserGroup;
use Tests\Feature\Auth\AuthTestCase;

class GroceryStoreUnavailableItemsAuthTest extends AuthTestCase
{
    /**
     * INDEX
     */
    public function test_get_grocery_store_unavailable_items_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsGET('api/grocery-store-unavailable-items');
    }

    /**
     * STORE
     */
    public function test_post_grocery_store_unavailable_item_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsPOST('api/grocery-store-unavailable-items');
    }

    /**
     * DESTROY
     */
    public function test_delete_grocery_store_unavailable_item_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $unavailableItem = $this->makeUnavailableItemForGoodUser();
        $this->runTestsDELETE("api/grocery-store-unavailable-items/{$unavailableItem->id}");
    }

    private function makeUnavailableItemForGoodUser(): GroceryStoreUnavailableItem
    {
        $group = UserGroup::factory()->grocery()->create();
        $group->members()->attach($this->goodUser->id, ['scope' => 'grocery']);

        $store = GroceryStore::factory()->create(['user_group_id' => $group->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $group->id]);

        /** @var GroceryStoreUnavailableItem $unavailableItem */
        $unavailableItem = GroceryStoreUnavailableItem::factory()->create([
            'user_group_id' => $group->id,
            'grocery_store_id' => $store->id,
            'grocery_item_id' => $item->id,
        ]);

        return $unavailableItem;
    }
}
