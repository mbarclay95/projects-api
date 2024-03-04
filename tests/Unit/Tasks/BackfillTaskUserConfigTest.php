<?php

namespace Tests\Unit\Tasks;

use App\Enums\FamilyTaskStrategyEnum;
use App\Models\Tasks\Family;
use App\Models\Tasks\TaskUserConfig;
use App\Models\Users\User;
use App\Repositories\Tasks\FamiliesRepository;
use App\Services\Tasks\BackfillTaskUserConfigService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Tests\TestCase;

class BackfillTaskUserConfigTest extends TestCase
{
    public function testAddingMember()
    {
        $this->generateAndAssertTaskUserConfigs(false);
    }

    public function testBackfillFromLastWeek()
    {
        $this->generateAndAssertTaskUserConfigs(true, 1);
    }

    public function testBackfillFrom6WeeksAgo()
    {
        $this->generateAndAssertTaskUserConfigs(true, 6);
    }

    private function generateAndAssertTaskUserConfigs(bool $shouldBackfill, int $numOfWeeks = 0): void
    {
        /** @var User $familyMember */
        $familyMember = User::factory()->create();
        /** @var Family $family */
        $family = $this->initFamilyAndMembers($familyMember);
        $date = Carbon::now('America/Los_Angeles');

        if ($shouldBackfill) {
            /** @var TaskUserConfig $config */
            $config = TaskUserConfig::query()->first();

            $date = $date->subWeeks($numOfWeeks);
            $config->start_date = (clone $date)->startOfWeek()->toDateString();
            $config->end_date = (clone $date)->endOfWeek()->toDateString();
            $config->save();

            BackfillTaskUserConfigService::run($family, (new User()));
        }

        $configs = TaskUserConfig::query()
                                 ->orderBy('end_date')
                                 ->get();
        // backfilling will include the upcoming week, so it will include 1 additional config
        $expectedConfigsCount = $numOfWeeks + ($shouldBackfill ? 2 : 1);

        $this->assertEquals($expectedConfigsCount , $configs->count());
        foreach ($configs as $config) {
            $this->assertEquals($config->user_id, $familyMember->id);
            $this->assertEquals($config->family_id, $family->id);
            $this->assertEquals($config->start_date, $date->startOfWeek()->toDateString());
            $this->assertEquals($config->end_date, $date->endOfWeek()->toDateString());
            $date = $date->addWeek();
        }
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
