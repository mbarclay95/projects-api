<?php

namespace App\Repositories\Tasks;

use App\Models\Tasks\Family;
use App\Models\Tasks\RecurringTask;
use App\Models\Tasks\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class TasksRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param User $user
     * @param bool $viewOnlyForUser
     * @return Collection|array
     */
    public function getEntities($request, User $user, bool $viewOnlyForUser): Collection|array
    {
        return Task::query()
                   ->where(function ($innerWhere) use ($user) {
                       $innerWhere
                           ->orWhere(function ($userWhere) use ($user) {
                               $userWhere->where('tasks.owner_type', '=', User::class)
                                         ->where('tasks.owner_id', '=', $user->id);
                           })
                           ->when($user->family, function ($familyCondition) use ($user) {
                               $familyCondition->orWhere(function ($familyWhere) use ($user) {
                                   $familyWhere->where('tasks.owner_type', '=', Family::class)
                                               ->where('tasks.owner_id', '=', $user->family->id);
                               });
                           });
                   })
                   ->orderBy('due_date')
                   ->with('tags', 'recurringTask')
                   ->filter($request)
                   ->get();
    }

    /**
     * @param $request
     * @param User $user
     * @return Model|array
     */
    public function createEntity($request, User $user): Model|array
    {
        $dueDate = Carbon::parse($request['dueDate'])->setTimezone('America/Los_Angeles')->startOfDay();
        if ($request['recurring']) {
            /** @var RecurringTask $recurringTask */
            $recurringTask = RecurringTask::createEntity($request, $user);
            $task = $recurringTask->createFutureTask($request['tags'], $dueDate);
        } else {
            $task = new Task([
                'name' => $request['name'],
                'description' => $request['description'] ?? null,
                'due_date' => $dueDate->toDateString(),
                'owner_type' => $request['ownerType'] === 'family' ? Family::class : User::class,
                'owner_id' => $request['ownerType'] === 'family' ? $user->family->id : $user->id,
                'priority' => $request['priority'],
            ]);
            if (array_key_exists('taskPoint', $request)) {
                $task->task_point = $request['taskPoint'];
            }
            $task->save();
            $task->updateTags($request['tags']);
        }

        return $task;
    }

    /**
     * @param Task $model
     * @param $request
     * @param User $user
     * @return Model|array
     */
    public function updateEntity(Model $model, $request, User $user): Model|array
    {
        $model->name = $request['name'];
        $model->description = $request['description'];
        $model->due_date = Carbon::parse($request['dueDate'])->setTimezone('America/Los_Angeles')->startOfDay()->toDateString();
        $model->owner_type = $request['ownerType'] === 'family' ? Family::class : User::class;
        $model->owner_id = $request['ownerId'];
        $model->priority = $request['priority'];
        if (array_key_exists('taskPoint', $request)) {
            $model->task_point = $request['taskPoint'];
        }

        if ($model->recurring_task_id) {
            $model->recurringTask->name = $request['name'];
            $model->recurringTask->description = $request['description'];
            $model->recurringTask->owner_type = $request['ownerType'] === 'family' ? Family::class : User::class;
            $model->recurringTask->owner_id = $request['ownerId'];
            $model->recurringTask->is_active = $request['isActive'];
            $model->recurringTask->priority = $request['priority'];
            $model->recurringTask->frequency_amount = $request['frequencyAmount'];
            $model->recurringTask->frequency_unit = $request['frequencyUnit'];
            if (array_key_exists('taskPoint', $request)) {
                $model->recurringTask->task_point = $request['taskPoint'];
            }
            $model->recurringTask->save();
        }

        $taskCompleted = false;
        if ($model->completed_at == null && isset($request['completedAt'])) {
            $model->completed_at = Carbon::parse($request['completedAt']);
            $model->completed_by_id = $user->id;
            $taskCompleted = true;
        }
        if ($model->completed_at && !isset($request['completedAt'])) {
            $model->completed_at = null;
            $model->completed_by_id = null;
            if ($model->recurring_task_id) {
                Task::query()
                    ->whereNull('completed_at')
                    ->whereNull('completed_by_id')
                    ->where('due_date', '>', $model->due_date)
                    ->where('recurring_task_id', '=', $model->recurring_task_id)
                    ->delete();
            }
        }
        $model->updateTags($request['tags']);
        $model->save();

        if ($taskCompleted) {
            $model->recurringTask?->createFutureTask($request['tags']);
        }

        return $model;
    }

    /**
     * @param Task $model
     * @param User $user
     * @return bool
     */
    public function destroyEntity(Model $model, User $user): bool
    {
        if ($model->recurring_task_id) {
            $model->recurringTask->delete();
        }
        $model->delete();

        return true;
    }
}
