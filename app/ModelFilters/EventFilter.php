<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class EventFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];

    public function showArchived($showArchived)
    {
        if ($showArchived) {
            $this->withTrashed();
        }
    }

    public function search($search)
    {
        if ($search && $search != '') {
            $this->where(function ($where) use ($search) {
                $wildcardSearch = '%' . $search . '%';
                $where->orWhere('name', 'ilike', $wildcardSearch)
                      ->orWhere('notes', 'ilike', $wildcardSearch);
            });
        }
    }
}
