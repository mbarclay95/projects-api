<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Class RecurringTask
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property string description
 * @property integer priority
 * @property integer frequency_amount
 * @property string frequency_unit
 * @property boolean is_active
 *
 * @property string owner_type
 * @property integer owner_id
 * @property User|Family owner
 *
 * @property integer task_point_id
 * @property TaskPoint taskPoint
 *
 * @property Collection|Tag[] tags
 */
class RecurringTask extends BaseApiModel
{
    use HasFactory, SoftDeletes;

    protected static array $apiModelAttributes = ['id', 'name', 'description', 'frequency_amount', 'frequency_unit',
        'owner_type', 'owner_id', 'is_active', 'priority'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    public static function createEntity($request, User $auth): RecurringTask
    {
        $task = new RecurringTask([
            'name' => $request['name'],
            'description' => $request['description'],
            'owner_type' => $request['ownerType'] === 'family' ? Family::class : User::class,
            'owner_id' => $request['ownerId'],
            'frequency_amount' => $request['frequencyAmount'],
            'frequency_unit' => $request['frequencyUnit'],
            'is_active' => true,
            'priority' => $request['priority'],
        ]);
        if (isset($request['taskPoint'])) {
            $task->taskPoint()->associate($request['taskPoint']['id']);
        }
        $task->save();

        return $task;
    }

    public function createFutureTask(array $tags, ?Carbon $dueDate = null): Task
    {
        $futureTask = Task::getFutureIncompleteTask($this->id);

        if ($futureTask) {
            return $futureTask;
        }

        if (!$dueDate) {
            $lastCompletedTask = Task::getLastCompletedTask($this->id);
            $dueDate = $this->incrementDateByFrequency($lastCompletedTask->completed_at);
        }

        return Task::createFromRecurring($this, $dueDate, $tags);
    }

    public function incrementDateByFrequency(Carbon $date): Carbon
    {
        if ($this->frequency_unit == 'day') {
            return $date->addDays($this->frequency_amount);
        }
        if ($this->frequency_unit == 'week') {
            return $date->addWeeks($this->frequency_amount);
        }

        return $date->addMonths($this->frequency_amount);
    }

    public function taskPoint(): BelongsTo
    {
        return $this->belongsTo(TaskPoint::class);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
