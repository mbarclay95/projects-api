<?php

namespace App\ModelFilters;

use App\Models\Tasks\Family;
use App\Models\Users\User;
use Carbon\Carbon;
use EloquentFilter\ModelFilter;

class TaskFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function numOfDays($numOfDays)
    {
        $nextNDays = Carbon::today('America/Los_Angeles')->addDays(intval($numOfDays))->toDateString();
        $this->where('due_date', '<=', $nextNDays);
    }

    public function completedStatus($completedStatus)
    {
        if ($completedStatus == 'completed') {
            $this->whereNotNull('completed_at');
        } elseif ($completedStatus == 'notCompleted') {
            $this->whereNull('completed_at');
        }
    }

    public function showPaused($showPaused)
    {
        if ($showPaused == 0) {
            $this->selectRaw('tasks.*')
                 ->leftJoin('recurring_tasks', 'tasks.recurring_task_id', '=', 'recurring_tasks.id')
                 ->where(function ($where) {
                     $where->orWhereNull('tasks.recurring_task_id')
                           ->orWhereHas('recurringTask', function ($has) {
                               $has->where('is_active', '=', true);
                           });
                 });
        }
    }

    public function recurringType($recurringType)
    {
        if ($recurringType == 1) {
            $this->whereNotNull('recurring_task_id');
        } elseif ($recurringType == 0) {
            $this->whereNull('recurring_task_id');
        }
    }

    public function ownerType($ownerType)
    {
        $ownerType = $ownerType === 'family' ? Family::class : User::class;
        $this->where('tasks.owner_type', '=', $ownerType);
    }

    public function ownerId($ownerId)
    {
        $this->where('tasks.owner_id', '=', $ownerId);
    }

    public function tags($tags)
    {
        $this->whereHas('tags', function ($has) use ($tags) {
            $has->whereIn('tag', $tags);
        });
    }

    public function search($search)
    {
        $this->where(function ($where) use ($search) {
            $where->orWhere('tasks.name', 'ilike', "%" . strtolower($search) . "%")
                  ->orWhere('tasks.description', 'ilike', "%" . strtolower($search) . "%");
        });
    }
}
