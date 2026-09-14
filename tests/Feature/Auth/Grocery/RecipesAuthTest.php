<?php

namespace Tests\Feature\Auth\Grocery;

use App\Enums\Roles;
use App\Models\Grocery\Recipe;
use App\Models\UserGroups\UserGroup;
use Tests\Feature\Auth\AuthTestCase;

class RecipesAuthTest extends AuthTestCase
{
    /**
     * INDEX
     */
    public function test_get_recipes_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsGET('api/recipes');
    }

    /**
     * STORE
     */
    public function test_post_recipe_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $this->runTestsPOST('api/recipes');
    }

    /**
     * UPDATE
     */
    public function test_put_recipe_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $group = UserGroup::factory()->grocery()->create();
        $group->members()->attach($this->goodUser->id, ['scope' => 'grocery']);
        /** @var Recipe $recipe */
        $recipe = Recipe::factory()->create(['user_group_id' => $group->id]);
        $this->runTestsPUT("api/recipes/{$recipe->id}");
    }

    /**
     * DESTROY
     */
    public function test_delete_recipe_user_permissions(): void
    {
        $this->initRoles([Roles::GROCERY_ROLE], []);
        $group = UserGroup::factory()->grocery()->create();
        $group->members()->attach($this->goodUser->id, ['scope' => 'grocery']);
        /** @var Recipe $recipe */
        $recipe = Recipe::factory()->create(['user_group_id' => $group->id]);
        $this->runTestsDELETE("api/recipes/{$recipe->id}");
    }
}
