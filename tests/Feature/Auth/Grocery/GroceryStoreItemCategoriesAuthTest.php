<?php

namespace Tests\Feature\Auth\Grocery;

use App\Enums\Roles;
use App\Models\Grocery\GroceryCategory;
use App\Models\Grocery\GroceryItem;
use App\Models\Grocery\GroceryStore;
use App\Models\Grocery\GroceryStoreItemCategory;
use App\Models\UserGroups\UserGroup;
use Tests\Feature\Auth\AuthTestCase;

class GroceryStoreItemCategoriesAuthTest extends AuthTestCase
{
    /**
     * INDEX
     */
    public function test_get_grocery_store_item_categories_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsGET('api/grocery-store-item-categories');
    }

    /**
     * STORE
     */
    public function test_post_grocery_store_item_category_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsPOST('api/grocery-store-item-categories');
    }

    /**
     * UPDATE
     */
    public function test_put_grocery_store_item_category_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $exception = $this->makeExceptionForGoodUser();
        $this->runTestsPUT("api/grocery-store-item-categories/{$exception->id}");
    }

    /**
     * DESTROY
     */
    public function test_delete_grocery_store_item_category_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $exception = $this->makeExceptionForGoodUser();
        $this->runTestsDELETE("api/grocery-store-item-categories/{$exception->id}");
    }

    private function makeExceptionForGoodUser(): GroceryStoreItemCategory
    {
        $group = UserGroup::factory()->grocery()->create();
        $group->members()->attach($this->goodUser->id, ['scope' => 'grocery']);

        $store = GroceryStore::factory()->create(['user_group_id' => $group->id]);
        $item = GroceryItem::factory()->create(['user_group_id' => $group->id]);
        $category = GroceryCategory::factory()->create(['user_group_id' => $group->id]);

        /** @var GroceryStoreItemCategory $exception */
        $exception = GroceryStoreItemCategory::factory()->create([
            'user_group_id' => $group->id,
            'grocery_store_id' => $store->id,
            'grocery_item_id' => $item->id,
            'grocery_category_id' => $category->id,
        ]);

        return $exception;
    }
}
