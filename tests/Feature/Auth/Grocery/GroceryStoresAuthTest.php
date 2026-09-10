<?php

namespace Tests\Feature\Auth\Grocery;

use App\Enums\Roles;
use App\Models\Grocery\GroceryStore;
use App\Models\UserGroups\UserGroup;
use Tests\Feature\Auth\AuthTestCase;

class GroceryStoresAuthTest extends AuthTestCase
{
    /**
     * INDEX
     */
    public function test_get_grocery_stores_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsGET('api/grocery-stores');
    }

    /**
     * STORE
     */
    public function test_post_grocery_store_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsPOST('api/grocery-stores');
    }

    /**
     * UPDATE
     */
    public function test_put_grocery_store_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $group = UserGroup::factory()->grocery()->create();
        $group->members()->attach($this->goodUser->id, ['scope' => 'grocery']);
        /** @var GroceryStore $store */
        $store = GroceryStore::factory()->create(['user_group_id' => $group->id]);
        $this->runTestsPUT("api/grocery-stores/{$store->id}");
    }

    /**
     * DESTROY
     */
    public function test_delete_grocery_store_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $group = UserGroup::factory()->grocery()->create();
        $group->members()->attach($this->goodUser->id, ['scope' => 'grocery']);
        /** @var GroceryStore $store */
        $store = GroceryStore::factory()->create(['user_group_id' => $group->id]);
        $this->runTestsDELETE("api/grocery-stores/{$store->id}");
    }
}
