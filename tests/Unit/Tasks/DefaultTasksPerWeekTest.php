<?php

namespace Tests\Unit\Tasks;

use App\Enums\FamilyTaskStrategyEnum;
use App\Models\Tasks\Family;
use App\Models\Tasks\TaskUserConfig;
use App\Models\Users\User;
use App\Repositories\Tasks\FamiliesRepository;
use App\Repositories\Tasks\TaskUserConfigsRepository;
use App\Services\Tasks\BackfillTaskUserConfigService;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class DefaultTasksPerWeekTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testFollowingWeekResetsToDefault()
    {
        /** @var User $familyMember */
        $familyMember = User::factory()->create();
        /** @var Family $family */
        $family = $this->initFamilyAndMembers($familyMember);

        /** @var TaskUserConfig $config */
        $config = TaskUserConfig::query()
                                ->orderBy('end_date', 'desc')
                                ->first();

        self::assertEquals($familyMember->id, $config->user_id);
        self::assertEquals(TaskUserConfigsRepository::DEFAULT_TASKS_PER_WEEK, $config->tasks_per_week);
        self::assertEquals(TaskUserConfigsRepository::DEFAULT_TASKS_PER_WEEK, $config->default_tasks_per_week);

        $config->tasks_per_week = 2;
        $config->save();

        BackfillTaskUserConfigService::run($family, $familyMember);
        /** @var TaskUserConfig $newConfig */
        $newConfig = TaskUserConfig::query()
                                   ->orderBy('end_date', 'desc')
                                   ->first();

        self::assertEquals($config->default_tasks_per_week, $newConfig->tasks_per_week);
    }

    private function initFamilyAndMembers(User $member): array|Model
    {
        $familyRequest = [
            'name' => 'test family',
            'taskStrategy' => FamilyTaskStrategyEnum::PER_TASK_POINT,
            'members' => [['id' => $member->id]]
        ];

        return FamiliesRepository::createEntityStatic($familyRequest, (new User()));

    }
}
