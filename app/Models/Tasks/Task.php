<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

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
 * @property Collection|Tag[] tags
 */
class Task extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'completed_at', 'cleared_at', 'due_date', 'description',
        'recurring_task_id', 'owner_type', 'owner_id'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    protected $dates = [
        'completed_at',
        'cleared_at',
        'due_date'
    ];

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTask::class);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public static function createEntity($request, int $authId)
    {

    }
}
