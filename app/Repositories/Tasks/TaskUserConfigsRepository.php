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
    protected static string|null $modelClass = TaskUserConfig::class;

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
                                  ->whereIn('user_id', $family->members->pluck('id'))
                                  ->get();

        if ($weekOffset === 0) {
            foreach ($family->members as $member) {
                $config = $entities->where('user_id', '=', $member->id)->first();
                if (!$config) {
                    /** @var TaskUserConfig $lastConfig */
                    $lastConfig = TaskUserConfig::query()
                                                ->where('user_id', '=', $member->id)
                                                ->orderBy('end_date', 'desc')
                                                ->first();
                    /** @var TaskUserConfig $newConfig */
                    $newConfig = $this->createEntity(['family' => $family, 'tasksPerWeek' => $lastConfig?->tasks_per_week ?? 5], $member);
                    $entities->add($newConfig);
                }
            }
        }

        /** @var TaskUserConfig $entity */
        foreach ($entities as $entity) {
            $entity->completedFamilyTasks = $entity->getCompletedFamilyTasks($date);
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
        $config->user()->associate($user);
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
