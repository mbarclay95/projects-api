<?php

namespace App\Models\Tasks;

use App\Models\ApiModels\FamilyMemberApiModel;
use App\Models\Users\User;
use Carbon\Carbon;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class Task
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property string description
 * @property integer priority
 * @property Carbon completed_at
 * @property Carbon cleared_at
 * @property Carbon due_date
 * @property integer task_point
 *
 * @property integer recurring_task_id
 * @property RecurringTask recurringTask
 *
 * @property string owner_type
 * @property integer owner_id
 * @property User|Family owner
 *
 * @property integer completed_by_id
 * @property User completedBy
 *
 * @property Collection|Tag[] tags
 */
class Task extends ApiModel
{
    use HasFactory, Filterable;

    protected static array $apiModelAttributes = ['id', 'name', 'completed_at', 'cleared_at', 'due_date', 'description',
        'owner_type', 'owner_id', 'frequency_amount', 'frequency_unit', 'recurring', 'is_active', 'priority', 'task_point'];
    protected static array $apiModelEntities = [
        'completedBy' => FamilyMemberApiModel::class,
    ];
    protected static array $apiModelArrayEntities = [
        'tags' => Tag::class
    ];
    protected $dateFormat = 'Y-m-d H:i:sO';

    protected $casts = [
        'completed_at' => 'datetime',
        'cleared_at' => 'datetime'
    ];

    public function updateTags(array $newTags): Task
    {
        $currentTags = $this->tags;
        $tagsToInsert = [];
        /** @var string $newTag */
        foreach ($newTags as $newTag) {
            if ($currentTags->doesntContain(function (Tag $currentTag) use ($newTag) {
                return $newTag == $currentTag->tag;
            })) {
                $tagsToInsert[] = new Tag(['tag' => $newTag]);
            }
        }
        $this->tags()->saveMany($tagsToInsert);

        foreach ($currentTags as $currentTag) {
            if (Collection::make($newTags)->doesntContain(function (string $newTag) use ($currentTag) {
                return $currentTag->tag == $newTag;
            })) {
                $this->tags()->detach($currentTag);
            }
        }

        $this->load('tags');

        return $this;
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public static function createFromRecurring(RecurringTask $recurringTask, Carbon $dueDate, array $tags): Task
    {
        $task = new Task([
            'name' => $recurringTask->name,
            'description' => $recurringTask->description,
            'due_date' => $dueDate->toDateString(),
            'owner_type' => $recurringTask->owner_type,
            'owner_id' => $recurringTask->owner_id,
            'priority' => $recurringTask->priority,
            'task_point' => $recurringTask->task_point
        ]);
        $task->recurringTask()->associate($recurringTask);
        $task->save();
        $task->updateTags($tags);

        return $task;
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTask::class);
    }

    /**
     * @param int $recurringTaskId
     * @return Model|Task
     */
    public static function getFutureIncompleteTask(int $recurringTaskId)
    {
        return Task::query()
                   ->where('recurring_task_id', '=', $recurringTaskId)
                   ->whereNull('completed_by_id')
                   ->orderBy('due_date')
                   ->first();
    }

    /**
     * @param int $recurringTaskId
     * @return Model|Task
     */
    public static function getLastCompletedTask(int $recurringTaskId)
    {
        return Task::query()
                   ->where('recurring_task_id', '=', $recurringTaskId)
                   ->whereNotNull('completed_by_id')
                   ->orderBy('due_date', 'desc')
                   ->first();
    }

    public function getDueDateAttribute($value): bool|Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $value, 'America/Los_Angeles')->startOfDay();
    }

    public function getFrequencyAmountAttribute(): ?int
    {
        return $this->recurringTask?->frequency_amount;
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->recurringTask?->is_active ?? true;
    }

    public function getFrequencyUnitAttribute(): ?string
    {
        return $this->recurringTask?->frequency_unit;

    }

    public function getRecurringAttribute(): bool
    {
        return !!$this->recurring_task_id;
    }

    public function getOwnerTypeAttribute($value): string
    {
        return $value == User::class ? 'user' : 'family';
    }

    public function getCompletedByNameAttribute(): string
    {
        return $this->completedBy?->name;
    }

    public function owner(): MorphTo
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
