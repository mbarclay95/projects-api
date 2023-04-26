<?php

namespace App\Repositories\Tasks;

use App\Models\Tasks\Family;
use App\Models\Tasks\TaskUserConfig;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class TaskUserConfigsRepository extends DefaultRepository
{
    public function getEntities($request, User $user, bool $viewOnlyForUser): Collection|array
    {
        $weekOffset = min($request['weekOffset'], 0);
        $date = Carbon::now('America/Los_Angeles')->addWeeks($weekOffset);
        /** @var Family $family */
        $family = Family::query()->find($request['familyId']);
        /** @var TaskUserConfig|Collection $entities */
        $entities = TaskUserConfig::query()
                                  ->where('family_id', '=', $request['familyId'])
                                  ->where('start_date', '<=', $date->toDateString())
                                  ->where('end_date', '>=', $date->toDateString())
                                  ->whereIn('user_id', $family->userConfigs->pluck('user_id'))
                                  ->with('user')
                                  ->get();

        $alreadyLoadedTasks = false;
        if ($weekOffset == 0 && $family->userConfigs->count() == 0) {
            $alreadyLoadedTasks = true;
            $lastWeek = Carbon::now('America/Los_Angeles')->subWeek()->toDateString();
            /** @var TaskUserConfig[] $currentConfigs */
            $currentConfigs = TaskUserConfig::query()
                                            ->where('family_id', '=', $request['familyId'])
                                            ->where('start_date', '<=', $lastWeek)
                                            ->where('end_date', '>=', $lastWeek)
                                            ->get();

            foreach ($currentConfigs as $currentConfig) {
                /** @var TaskUserConfig $newConfig */
                $newConfig = $this->createEntity(['family' => $family, 'tasksPerWeek' => $currentConfig->tasks_per_week, 'user_id' => $currentConfig->user_id], $user);
                $entities->add($newConfig);
            }
        }

        if (!$alreadyLoadedTasks) {
            /** @var TaskUserConfig $entity */
            foreach ($entities as $entity) {
                $entity->completedFamilyTasks = $entity->getCompletedFamilyTasks($date);
            }
        }

        return $entities;
    }

    public function createEntity($request, User $user): Model|array
    {
        $date = Carbon::now('America/Los_Angeles');
        $config = new TaskUserConfig([
            'tasks_per_week' => $request['tasksPerWeek'] ?? 5,
            'start_date' => array_key_exists('startDate', $request) ? $request['startDate'] : $date->startOfWeek()->toDateString(),
            'end_date' => array_key_exists('endDate', $request) ? $request['endDate'] : $date->endOfWeek()->toDateString(),
        ]);
        $config->family()->associate($request['family']);
        $config->user()->associate($request['user_id']);
        $config->save();

        $config->completedFamilyTasks = $config->getCompletedFamilyTasks($date);

        return $config;
    }

    /**
     * @param TaskUserConfig $model
     * @param $request
     * @param User $user
     * @return Model|array
     */
    public function updateEntity(Model $model, $request, User $user): Model|array
    {
        $model->tasks_per_week = $request['tasksPerWeek'];
        $model->save();
        $model->completedFamilyTasks = $model->getCompletedFamilyTasks(Carbon::now('America/Los_Angeles'));

        return $model;
    }
}
