<?php

namespace Tests\Feature\Grocery;

use App\Enums\Roles;
use App\Models\Grocery\GroceryCategory;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GroceryCategoriesTest extends TestCase
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

    public function test_the_index_returns_the_callers_groups_categories_ordered_by_name(): void
    {
        GroceryCategory::factory()->create(['user_group_id' => $this->group->id, 'name' => 'produce']);
        GroceryCategory::factory()->create(['user_group_id' => $this->group->id, 'name' => 'bakery']);

        $names = collect($this->jsonAs($this->user, 'GET', 'api/grocery-categories')
            ->assertSuccessful()
            ->json())->pluck('name');

        self::assertEquals(['bakery', 'produce'], $names->all());
    }

    public function test_the_index_excludes_another_grocery_groups_categories(): void
    {
        $otherGroup = UserGroup::factory()->grocery()->create();
        $otherCategory = GroceryCategory::factory()->create(['user_group_id' => $otherGroup->id, 'name' => 'stranger category']);

        $names = collect($this->jsonAs($this->user, 'GET', 'api/grocery-categories')
            ->assertSuccessful()
            ->json())->pluck('name');

        self::assertFalse($names->contains($otherCategory->name));
    }

    public function test_the_index_is_empty_for_a_caller_with_no_grocery_group(): void
    {
        $groupless = User::factory()->create();
        $groupless->assignRole(Roles::GROCERY_ROLE);

        $this->jsonAs($groupless, 'GET', 'api/grocery-categories')
            ->assertSuccessful()
            ->assertJson([]);
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
