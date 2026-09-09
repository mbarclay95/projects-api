<?php

namespace Tests\Feature\UserGroups;

use App\Enums\Roles;
use App\Models\Tasks\TaskUserConfig;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GroupScopeTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Roles::ADMIN_ROLE);
    }

    public function test_a_grocery_group_can_be_created_with_no_task_settings(): void
    {
        $member = User::factory()->create();

        $group = $this->jsonAs($this->admin, 'POST', 'api/user-groups', [
            'name' => 'test grocery group',
            'scope' => 'grocery',
            'members' => [['id' => $member->id]],
        ])->assertSuccessful()->json();

        self::assertEquals('grocery', DB::table('user_group_user')
            ->where('user_group_id', $group['id'])
            ->where('user_id', $member->id)
            ->value('scope'));
        self::assertEquals(0, TaskUserConfig::query()->where('user_group_id', $group['id'])->count());
    }

    public function test_task_strategy_is_required_for_tasks_and_not_for_grocery(): void
    {
        $member = User::factory()->create();

        $this->jsonAs($this->admin, 'POST', 'api/user-groups', [
            'name' => 'test family',
            'scope' => 'tasks',
            'members' => [['id' => $member->id]],
        ])->assertStatus(422);

        $this->jsonAs($this->admin, 'POST', 'api/user-groups', [
            'name' => 'test grocery group',
            'scope' => 'grocery',
            'members' => [['id' => $member->id]],
        ])->assertSuccessful();
    }

    public function test_no_scope_or_an_unknown_scope_is_rejected(): void
    {
        $member = User::factory()->create();

        $this->jsonAs($this->admin, 'POST', 'api/user-groups', [
            'name' => 'test group',
            'members' => [['id' => $member->id]],
        ])->assertStatus(422);

        $this->jsonAs($this->admin, 'POST', 'api/user-groups', [
            'name' => 'test group',
            'scope' => 'nonsense',
            'members' => [['id' => $member->id]],
        ])->assertStatus(422);
    }

    public function test_the_grocery_response_hides_task_fields_and_the_tasks_response_has_them(): void
    {
        $member = User::factory()->create();

        $grocery = $this->jsonAs($this->admin, 'POST', 'api/user-groups', [
            'name' => 'test grocery group',
            'scope' => 'grocery',
            'members' => [['id' => $member->id]],
        ])->assertSuccessful()->json();

        foreach (['taskStrategy', 'taskPoints', 'tasksPerWeek', 'totalFamilyTasks', 'minWeekOffset', 'minYear'] as $key) {
            self::assertArrayNotHasKey($key, $grocery);
        }

        $family = $this->jsonAs($this->admin, 'POST', 'api/user-groups', [
            'name' => 'test family',
            'scope' => 'tasks',
            'taskStrategy' => 'per task point',
            'members' => [['id' => $member->id]],
        ])->assertSuccessful()->json();

        foreach (['taskStrategy', 'taskPoints', 'tasksPerWeek', 'totalFamilyTasks', 'minWeekOffset', 'minYear'] as $key) {
            self::assertArrayHasKey($key, $family);
        }
    }

    public function test_scope_in_the_update_body_does_not_change_the_stored_scope(): void
    {
        $group = UserGroup::factory()->create(['scope' => 'grocery']);

        $this->jsonAs($this->admin, 'PUT', 'api/user-groups/'.$group->id, [
            'name' => $group->name,
            'scope' => 'tasks',
            'members' => [],
        ])->assertSuccessful();

        self::assertEquals('grocery', $group->fresh()->scope);
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
