<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * Class Tag
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string tag
 *
 * @property Collection|Task[] tasks
 * @property Collection|RecurringTask[] recurringTasks
 */
class Tag extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'tag'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    public function tasks(): MorphToMany
    {
        return $this->morphedByMany(Task::class, 'taggable');
    }

    public function recurringTasks(): MorphToMany
    {
        return $this->morphedByMany(RecurringTask::class, 'taggable');
    }
}
