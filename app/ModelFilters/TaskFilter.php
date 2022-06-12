<?php

namespace App\ModelFilters;

use App\Models\Tasks\Family;
use App\Models\User;
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
        $nextNDays = Carbon::today()->addDays($numOfDays);
        $this->where('due_date', '<', $nextNDays);
    }

    public function completedStatus($completedStatus)
    {
        if ($completedStatus == 'completed') {
            $this->whereNotNull('completed_at');
        } elseif ($completedStatus == 'notCompleted') {
            $this->whereNull('completed_at');
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
        $this->where('owner_type', '=', $ownerType);
    }

    public function ownerId($ownerId)
    {
        $this->where('owner_id', '=', $ownerId);
    }
}
