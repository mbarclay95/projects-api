<?php

namespace App\Models\Tags;

use App\Models\Grocery\GroceryItem;
use App\Models\Tasks\{RecurringTask, Task};
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
 * @property Collection|GroceryItem[] groceryItems
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

    public function groceryItems(): MorphToMany
    {
        return $this->morphedByMany(GroceryItem::class, 'taggable');
    }
}
