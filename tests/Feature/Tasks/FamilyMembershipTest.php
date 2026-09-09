<?php

namespace Tests\Feature\Tasks;

use App\Enums\FamilyTaskStrategyEnum;
use App\Enums\Roles;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskUserConfig;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use App\Repositories\UserGroups\UserGroupsRepository;
use App\Services\Tasks\BackfillTaskUserConfigService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class FamilyMembershipTest extends TestCase
{
    public function test_membership_paths_hold_before_and_after_removing_a_member(): void
    {
        $memberOne = User::factory()->create();
        $memberTwo = User::factory()->create();
        $memberOne->assignRole(Roles::TASK_ROLE);
        $memberTwo->assignRole(Roles::TASK_ROLE);

        $family = UserGroupsRepository::createEntityStatic([
            'name' => 'test family',
            'taskStrategy' => FamilyTaskStrategyEnum::PER_TASK_POINT->value,
            'members' => [['id' => $memberOne->id], ['id' => $memberTwo->id]],
        ], new User);

        self::assertEquals(2, DB::table('user_group_user')->where('user_group_id', $family->id)->count());

        // Read on an instance that is never saved, in this order: reading
        // min_week_offset dirties min_year, and saving this instance afterwards
        // would fail since `user_groups` has no min_year column.
        $forOffsetCheck = $family->fresh();
        self::assertEquals(0, $forOffsetCheck->min_week_offset);
        self::assertEquals(Carbon::now()->year, $forOffsetCheck->min_year);

        // A week in the past keeps this row stable regardless of what day
        // the suite runs.
        $pastStart = Carbon::now('America/Los_Angeles')->subWeeks(2)->startOfWeek()->toDateString();
        $pastEnd = Carbon::now('America/Los_Angeles')->subWeeks(2)->endOfWeek()->toDateString();
        TaskUserConfig::factory()->create([
            'user_id' => $memberOne->id,
            'user_group_id' => $family->id,
            'start_date' => $pastStart,
            'end_date' => $pastEnd,
        ]);
        TaskUserConfig::factory()->create([
            'user_id' => $memberTwo->id,
            'user_group_id' => $family->id,
            'start_date' => $pastStart,
            'end_date' => $pastEnd,
        ]);

        $memberIds = $family->fresh()->members->pluck('id');
        self::assertTrue($memberIds->contains($memberOne->id));
        self::assertTrue($memberIds->contains($memberTwo->id));

        self::assertEquals($family->id, $memberOne->taskGroup->id);
        self::assertEquals($family->id, $memberOne->task_group_id);
        self::assertEquals($family->id, $memberTwo->taskGroup->id);
        self::assertEquals($family->id, $memberTwo->task_group_id);

        $statsIds = collect($this->jsonAs($memberOne, 'GET', 'api/family-stats?userGroupId='.$family->id.'&year='.Carbon::now()->year)
            ->assertSuccessful()
            ->json())->pluck('id');
        self::assertCount(2, $statsIds);
        self::assertTrue($statsIds->contains($memberOne->id));
        self::assertTrue($statsIds->contains($memberTwo->id));

        $configRows = $this->jsonAs($memberOne, 'GET', 'api/task-user-config?userGroupId='.$family->id.'&weekOffset=0')
            ->assertSuccessful()
            ->json();
        self::assertCount(2, $configRows);

        $familyTask = Task::factory()->create([
            'owner_type' => (new UserGroup)->getMorphClass(),
            'owner_id' => $family->id,
        ]);
        $this->jsonAs($memberOne, 'GET', 'api/tasks/'.$familyTask->id.'/history')->assertSuccessful();

        $stranger = User::factory()->create();
        $stranger->assignRole(Roles::TASK_ROLE);
        $this->jsonAs($stranger, 'GET', 'api/tasks/'.$familyTask->id.'/history')->assertUnauthorized();

        UserGroupsRepository::updateEntityStatic($family->fresh(), [
            'name' => $family->name,
            'taskStrategy' => $family->task_strategy->value,
            'members' => [['id' => $memberTwo->id]],
        ], new User);

        $remainingIds = $family->fresh()->members->pluck('id');
        self::assertCount(1, $remainingIds);
        self::assertTrue($remainingIds->contains($memberTwo->id));

        self::assertNull($memberOne->fresh()->taskGroup);
        self::assertEquals($family->id, $memberTwo->fresh()->taskGroup->id);

        self::assertEquals(0, DB::table('user_group_user')->where('user_group_id', $family->id)->where('user_id', $memberOne->id)->count());
        self::assertNotNull(TaskUserConfig::query()
            ->where('user_group_id', '=', $family->id)
            ->where('user_id', '=', $memberOne->id)
            ->where('start_date', '=', $pastStart)
            ->first());
        $today = Carbon::now('America/Los_Angeles')->toDateString();
        self::assertEquals(0, TaskUserConfig::query()
            ->where('user_group_id', '=', $family->id)
            ->where('user_id', '=', $memberOne->id)
            ->where('end_date', '>=', $today)
            ->count());
    }

    public function test_a_family_with_lapsed_configs_still_reports_all_members(): void
    {
        $memberOne = User::factory()->create();
        $memberTwo = User::factory()->create();

        $family = UserGroupsRepository::createEntityStatic([
            'name' => 'test family',
            'taskStrategy' => FamilyTaskStrategyEnum::PER_TASK_POINT->value,
            'members' => [['id' => $memberOne->id], ['id' => $memberTwo->id]],
        ], new User);

        TaskUserConfig::query()
            ->where('user_group_id', '=', $family->id)
            ->update(['end_date' => Carbon::now('America/Los_Angeles')->subWeeks(2)->toDateString()]);

        $memberIds = $family->fresh()->members->pluck('id');
        self::assertCount(2, $memberIds);
        self::assertTrue($memberIds->contains($memberOne->id));
        self::assertTrue($memberIds->contains($memberTwo->id));
    }

    public function test_removing_the_only_member_does_not_resurrect_them_via_backfill(): void
    {
        $member = User::factory()->create();

        $family = UserGroupsRepository::createEntityStatic([
            'name' => 'test family',
            'taskStrategy' => FamilyTaskStrategyEnum::PER_TASK_POINT->value,
            'members' => [['id' => $member->id]],
        ], new User);

        UserGroupsRepository::updateEntityStatic($family->fresh(), [
            'name' => $family->name,
            'taskStrategy' => $family->task_strategy->value,
            'members' => [],
        ], new User);

        $countBefore = TaskUserConfig::query()
            ->where('user_group_id', '=', $family->id)
            ->where('user_id', '=', $member->id)
            ->count();

        $newConfigs = BackfillTaskUserConfigService::run($family->fresh(), $member);

        self::assertCount(0, $newConfigs);
        self::assertEquals($countBefore, TaskUserConfig::query()
            ->where('user_group_id', '=', $family->id)
            ->where('user_id', '=', $member->id)
            ->count());
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
