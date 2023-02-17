<?php

namespace App\ModelFilters;

use Carbon\Carbon;
use EloquentFilter\ModelFilter;

class GoalDayFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function weekOffset(int $weekOffset)
    {
        $date = Carbon::now()->setTimezone('America/Los_Angeles')->addWeeks($weekOffset);
        $this->where('date', '>=', $date->startOfWeek()->toDateString())
             ->where('date', '<=', $date->endOfWeek()->toDateString());
    }
}
