<?php

namespace App\Services\Tasks;

use App\Models\Tasks\Family;
use App\Models\Tasks\TaskUserConfig;
use App\Models\Users\User;
use App\Repositories\Tasks\TaskUserConfigsRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class BackfillTaskUserConfigService
{
    /**
     * @param Family $family
     * @param User $user
     * @return Collection
     */
    public static function run(Family $family, Authenticatable $user): Collection
    {
        $newConfigs = new Collection();

        $lastConfigsAndDate = static::getLastConfigsAndDate($family);
        if (!$lastConfigsAndDate) {
            return $newConfigs;
        }

        [$lastConfigs, $date] = $lastConfigsAndDate;
        /** @var Carbon $newConfigWeek */
        $newConfigWeek = $date->addWeek();
        $endOfThisWeek = Carbon::now('America/Los_Angeles')->endOfWeek();

        while ($endOfThisWeek->greaterThan($newConfigWeek)) {
            /** @var TaskUserConfig $lastConfig */
            foreach ($lastConfigs as $lastConfig) {
                $configParams = [
                    'family' => $family,
                    'tasksPerWeek' => $lastConfig->tasks_per_week,
                    'userId' => $lastConfig->user_id,
                    'startDate' => (clone $newConfigWeek)->startOfWeek()->toDateString(),
                    'endDate' => (clone $newConfigWeek)->endOfWeek()->toDateString(),
                ];
                /** @var TaskUserConfig $newConfig */
                $newConfig = TaskUserConfigsRepository::createEntityStatic($configParams, $user);
                $newConfig->setRelation('user', $lastConfig->user);
                $newConfigs->add($newConfig);
            }
            $newConfigWeek = $newConfigWeek->addWeek();
        }

        return $newConfigs;
    }

    private static function getLastConfigsAndDate(Family $family): array|null
    {
        /** @var TaskUserConfig $mostRecentConfig */
        $mostRecentConfig = TaskUserConfig::query()
                                          ->where('family_id', '=', $family->id)
                                          ->orderBy('end_date', 'desc')
                                          ->first();
        if (!$mostRecentConfig) {
            return null;
        }

        // SubDay so we are for sure inside the week
        $date = Carbon::parse($mostRecentConfig->end_date)->timezone('America/Los_Angeles')->subDay();

        $configs = TaskUserConfig::query()
                                 ->with('user')
                                 ->where('family_id', '=', $family->id)
                                 ->where('start_date', '<=', $date->toDateString())
                                 ->where('end_date', '>=', $date->toDateString())
                                 ->get();

        return [$configs, $date];
    }
}
