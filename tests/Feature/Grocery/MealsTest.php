<?php

namespace Tests\Feature\Grocery;

use App\Enums\MealSlot;
use App\Enums\Roles;
use App\Models\Grocery\Meal;
use App\Models\Grocery\Recipe;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class MealsTest extends TestCase
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

    public function test_the_index_returns_the_week_inclusive_of_both_ends(): void
    {
        foreach (['2026-10-11', '2026-10-12', '2026-10-18', '2026-10-19'] as $date) {
            Meal::factory()->create(['user_group_id' => $this->group->id, 'date' => $date]);
        }

        $response = $this->jsonAs($this->user, 'GET', 'api/meals?weekStart=2026-10-12')->assertSuccessful();

        self::assertEquals(['2026-10-12', '2026-10-18'], array_column($response->json(), 'date'));
    }

    public function test_the_index_excludes_another_groups_meals(): void
    {
        $other = UserGroup::factory()->grocery()->create();
        Meal::factory()->create(['user_group_id' => $other->id, 'date' => '2026-10-13']);
        Meal::factory()->create(['user_group_id' => $this->group->id, 'date' => '2026-10-13']);

        $this->jsonAs($this->user, 'GET', 'api/meals?weekStart=2026-10-12')
            ->assertSuccessful()
            ->assertJsonCount(1);
    }

    public function test_the_index_is_empty_for_a_caller_with_no_grocery_group(): void
    {
        $loner = User::factory()->create();
        $loner->assignRole(Roles::GROCERY_ROLE);
        Meal::factory()->create(['user_group_id' => $this->group->id, 'date' => '2026-10-13']);

        $this->jsonAs($loner, 'GET', 'api/meals?weekStart=2026-10-12')
            ->assertSuccessful()
            ->assertExactJson([]);
    }

    public function test_the_index_requires_week_start(): void
    {
        $this->jsonAs($this->user, 'GET', 'api/meals')
            ->assertUnprocessable()
            ->assertJsonValidationErrors('weekStart');
    }

    public function test_a_dish_serialises_to_the_documented_shape(): void
    {
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id]);
        $meal = Meal::factory()->create([
            'user_group_id' => $this->group->id,
            'date' => '2026-10-12',
            'slot' => MealSlot::DINNER,
            'position' => 0,
            'name' => 'Tacos',
            'recipe_id' => $recipe->id,
        ]);

        $this->jsonAs($this->user, 'GET', 'api/meals?weekStart=2026-10-12')
            ->assertSuccessful()
            ->assertExactJson([[
                'id' => $meal->id,
                'date' => '2026-10-12',
                'slot' => 'dinner',
                'position' => 0,
                'name' => 'Tacos',
                'recipeId' => $recipe->id,
            ]]);
    }

    public function test_a_put_creates_the_dishes_with_positions_per_slot(): void
    {
        $response = $this->putDay([
            ['slot' => 'dinner', 'name' => 'Tacos'],
            ['slot' => 'dinner', 'name' => 'rice'],
            ['slot' => 'breakfast', 'name' => 'Oatmeal'],
        ])->assertSuccessful();

        $rows = Meal::query()->where('user_group_id', '=', $this->group->id)->where('date', '=', '2026-10-12')->get();
        self::assertCount(3, $rows);
        $byName = $rows->keyBy('name');
        self::assertSame(0, $byName['Tacos']->position);
        self::assertSame(1, $byName['rice']->position);
        self::assertSame(0, $byName['Oatmeal']->position);
        self::assertEqualsCanonicalizing(['Tacos', 'rice', 'Oatmeal'], array_column($response->json(), 'name'));
    }

    public function test_a_put_replaces_only_that_groups_dishes_on_that_date(): void
    {
        $other = UserGroup::factory()->grocery()->create();
        Meal::factory()->create(['user_group_id' => $this->group->id, 'date' => '2026-10-12', 'name' => 'old']);
        $nextDay = Meal::factory()->create(['user_group_id' => $this->group->id, 'date' => '2026-10-13']);
        $otherGroups = Meal::factory()->create(['user_group_id' => $other->id, 'date' => '2026-10-12']);

        $this->putDay([['slot' => 'lunch', 'name' => 'Soup']])->assertSuccessful();

        self::assertDatabaseMissing('meals', ['name' => 'old']);
        self::assertDatabaseHas('meals', ['id' => $nextDay->id]);
        self::assertDatabaseHas('meals', ['id' => $otherGroups->id]);
        self::assertDatabaseHas('meals', ['name' => 'Soup', 'date' => '2026-10-12']);
    }

    public function test_a_put_with_no_meals_clears_the_date(): void
    {
        Meal::factory()->create(['user_group_id' => $this->group->id, 'date' => '2026-10-12']);

        $this->putDay([])->assertSuccessful()->assertExactJson([]);

        self::assertSame(0, Meal::query()->where('date', '=', '2026-10-12')->count());
    }

    public function test_a_put_links_a_recipe_by_trimmed_case_insensitive_name(): void
    {
        $recipe = Recipe::factory()->create(['user_group_id' => $this->group->id, 'name' => 'Tacos']);

        $this->putDay([['slot' => 'dinner', 'name' => '  tacos ']])->assertSuccessful();

        $meal = Meal::query()->firstOrFail();
        self::assertSame('tacos', $meal->name);
        self::assertSame($recipe->id, $meal->recipe_id);
    }

    public function test_a_put_does_not_link_another_groups_recipe(): void
    {
        $other = UserGroup::factory()->grocery()->create();
        Recipe::factory()->create(['user_group_id' => $other->id, 'name' => 'Tacos']);

        $this->putDay([['slot' => 'dinner', 'name' => 'Tacos']])->assertSuccessful();

        self::assertNull(Meal::query()->firstOrFail()->recipe_id);
    }

    public function test_a_put_from_a_caller_with_no_grocery_group_is_a_422(): void
    {
        $loner = User::factory()->create();
        $loner->assignRole(Roles::GROCERY_ROLE);

        $this->jsonAs($loner, 'PUT', 'api/meals/days/2026-10-12', ['meals' => [['slot' => 'dinner', 'name' => 'Tacos']]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('meals');

        self::assertSame(0, Meal::query()->count());
    }

    public function test_a_put_with_an_unknown_slot_is_a_422(): void
    {
        $this->putDay([['slot' => 'brunch', 'name' => 'Eggs']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('meals.0.slot');
    }

    public function test_a_put_to_an_impossible_date_is_a_422(): void
    {
        $this->jsonAs($this->user, 'PUT', 'api/meals/days/2026-02-30', ['meals' => []])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');
    }

    public function test_a_put_with_a_blank_name_is_a_422(): void
    {
        $this->putDay([['slot' => 'dinner', 'name' => '']])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('meals.0.name');
    }

    private function putDay(array $meals): TestResponse
    {
        return $this->jsonAs($this->user, 'PUT', 'api/meals/days/2026-10-12', ['meals' => $meals]);
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
