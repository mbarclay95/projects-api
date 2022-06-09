<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use JetBrains\PhpStorm\Pure;

/**
 * Class Task
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property string description
 * @property Carbon completed_at
 * @property Carbon cleared_at
 * @property Carbon due_date
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
class Task extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'completed_at', 'cleared_at', 'due_date', 'description',
        'owner_type', 'owner_id', 'frequency_amount', 'frequency_unit', 'recurring'];
    protected static array $apiModelEntities = [
        'completedBy' => User::class
    ];
    protected static array $apiModelArrayEntities = [];
    protected $dateFormat = 'Y-m-d H:i:sO';

//    protected $dates = [
//        'completed_at',
//        'cleared_at'
//    ];

    /**
     * @param string $attributeKey
     * @param Task $model
     * @return mixed|void
     */
    #[Pure] public static function buildFromAttributes(string $attributeKey, Model $model)
    {
        if ($attributeKey == 'owner_type') {
            return $model->owner_type == User::class ? 'user' : 'family';
        }

        return parent::buildFromAttributes($attributeKey, $model);
    }

    public static function getUserEntities($request, User $auth)
    {
        $next7Days = Carbon::today()->addDays(7);
        return Task::query()
                   ->where(function ($innerWhere) use ($auth) {
                       $innerWhere
                           ->orWhere(function ($userWhere) use ($auth) {
                               $userWhere->where('owner_type', '=', User::class)
                                         ->where('owner_id', '=', $auth->id);
                           })
                           ->when($auth->family, function ($familyCondition) use ($auth) {
                               $familyCondition->orWhere(function ($familyWhere) use ($auth) {
                                   $familyWhere->where('owner_type', '=', Family::class)
                                               ->where('owner_id', '=', $auth->family->id);
                               });
                           });
                   })
                   ->where('due_date', '<', $next7Days)
                   ->whereNull('completed_at')
                   ->whereNull('cleared_at')
                   ->orderBy('due_date')
                   ->limit(10)
                   ->get();
    }

    /**
     * @param $request
     * @param User $auth
     * @return Task|RecurringTask
     */
    public static function createEntity($request, User $auth): Task|RecurringTask
    {
        $dueDate = Carbon::parse($request['dueDate'])->setTimezone('America/Los_Angeles')->startOfDay();
        if ($request['recurring']) {
            $recurringTask = RecurringTask::createEntity($request, $auth);
            $task = $recurringTask->createFutureTask($dueDate);
        } else {
            $task = new Task([
                'name' => $request['name'],
                'description' => $request['description'] ?? null,
                'due_date' => $dueDate->toDateString(),
                'owner_type' => $request['ownerType'] === 'family' ? Family::class : User::class,
                'owner_id' => $request['ownerId'],
            ]);
            $task->save();
        }

        return $task;
    }

    /**
     * @param Task $entity
     * @param $request
     * @return Task
     */
    public static function updateEntity(Model $entity, $request): Task
    {
        $entity->name = $request['name'];
        $entity->description = $request['description'];
        $entity->due_date = Carbon::parse($request['dueDate'])->setTimezone('America/Los_Angeles')->startOfDay()->toDateString();
        $entity->owner_type = $request['ownerType'] === 'family' ? Family::class : User::class;
        $entity->owner_id = $request['ownerId'];

        $taskCompleted = false;
        if ($entity->completed_at == null && isset($request['completedAt'])) {
            $entity->completed_at = Carbon::parse($request['completedAt']);
            $entity->completed_by_id = Auth::id();
            $taskCompleted = true;
        }
        $entity->save();

        if ($taskCompleted) {
            $entity->recurringTask?->createFutureTask();
        }

        return $entity;
    }

    public static function createFromRecurring(RecurringTask $recurringTask, Carbon $dueDate): Task
    {
        $task = new Task([
            'name' => $recurringTask->name,
            'description' => $recurringTask->description,
            'due_date' => $dueDate->toDateString(),
            'owner_type' => $recurringTask->owner_type,
            'owner_id' => $recurringTask->owner_id,
        ]);
        $task->recurringTask()->associate($recurringTask);
        $task->save();

        return $task;
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTask::class);
    }

    /**
     * @param int $recurringTaskId
     * @return Task
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
     * @return Task
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

    public function getFrequencyUnitAttribute(): ?string
    {
        return $this->recurringTask?->frequency_unit;

    }

    public function getRecurringAttribute(): bool
    {
        return !!$this->recurringTask;
    }

    public function owner(): MorphTo
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
