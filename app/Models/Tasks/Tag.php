<?php

namespace App\Models\Tasks;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class Tag
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string tag
 * @property Collection|Task[] tasks
 * @property Collection|RecurringTask[] recurringTasks
 */
class Tag extends ApiModel
{
    use HasFactory;

    /**
     * @param  Tag|null  $model
     */
    public static function toApiModel(?Model $model, array $hideItem = []): array|null|string
    {
        return $model->tag;
    }

    public function tasks(): MorphToMany
    {
        return $this->morphedByMany(Task::class, 'taggable');
    }

    public function recurringTasks(): MorphToMany
    {
        return $this->morphedByMany(RecurringTask::class, 'taggable');
    }
}
