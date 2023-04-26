<?php

namespace App\Repositories\Tasks;

use App\Models\Tasks\Task;
use App\Models\User;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class TaskHistoriesRepository extends DefaultRepository
{
    public function getEntities($request, User $user, bool $viewOnlyForUser): Collection|array
    {
        /** @var Task $task */
        $task = Task::query()->find($request['taskId']);
        if (!$task || !$task->recurring_task_id) {
            return [];
        }

        return Task::query()
                   ->where('recurring_task_id', '=', $task->recurring_task_id)
                   ->whereNotNull('completed_at')
                   ->with('completedBy')
                   ->orderBy('completed_at', 'desc')
                   ->get();
    }
}
