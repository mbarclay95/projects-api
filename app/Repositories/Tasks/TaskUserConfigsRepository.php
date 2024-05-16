<?php

namespace App\Repositories\Tasks;

use App\Models\Tasks\Family;
use App\Models\Tasks\TaskUserConfig;
use App\Models\Users\User;
use App\Services\Tasks\BackfillTaskUserConfigService;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class TaskUserConfigsRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        $weekOffset = min($request['weekOffset'], 1);
        $date = Carbon::now('America/Los_Angeles')->addWeeks($weekOffset);
        /** @var Family $family */
        $family = Family::query()
                        ->with('userConfigs')
                        ->find($request['familyId']);
        /** @var TaskUserConfig|Collection $entities */
        $entities = TaskUserConfig::query()
                                  ->where('family_id', '=', $request['familyId'])
                                  ->where('start_date', '<=', $date->toDateString())
                                  ->where('end_date', '>=', $date->toDateString())
                                  ->whereIn('user_id', $family->userConfigs->pluck('user_id'))
                                  ->with('user')
                                  ->orderBy('user_id')
                                  ->get();

        $alreadyLoadedTasks = false;
        if (($weekOffset == 0 || $weekOffset == 1) && $entities->count() == 0) {
            $alreadyLoadedTasks = true;
            $entities = BackfillTaskUserConfigService::run($family, $user);
        }

        if (!$alreadyLoadedTasks) {
            /** @var TaskUserConfig $entity */
            foreach ($entities as $entity) {
                $entity->completedFamilyTasks = $entity->getCompletedFamilyTasks($date, $entity->user);
            }
        }

        return $entities;
    }

    public function createEntity($request, Authenticatable $user): Model|array
    {
        $date = Carbon::now('America/Los_Angeles');
        /** @var User $configUser */
        $configUser = $request['user'];
        $config = new TaskUserConfig([
            'tasks_per_week' => $request['tasksPerWeek'] ?? 5,
            'start_date' => array_key_exists('startDate', $request) ? $request['startDate'] : $date->startOfWeek()->toDateString(),
            'end_date' => array_key_exists('endDate', $request) ? $request['endDate'] : $date->endOfWeek()->toDateString(),
        ]);
        $config->family()->associate($request['family']);
        $config->user()->associate($configUser);
        $config->save();

        $config->completedFamilyTasks = $config->getCompletedFamilyTasks($date, $configUser);

        return $config;
    }

    /**
     * @param TaskUserConfig $model
     * @param $request
     * @param Authenticatable $user
     * @return Model|array
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->tasks_per_week = $request['tasksPerWeek'];
        $model->save();
        $date = Carbon::parse($model->start_date, 'America/Los_Angeles')->addDay();
        $model->completedFamilyTasks = $model->getCompletedFamilyTasks($date, $model->user);

        return $model;
    }
}
