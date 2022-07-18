<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Family
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property integer tasks_per_week
 * @property string color
 *
 * @property integer family_id
 * @property Family family
 *
 * @property integer user_id
 * @property User user
 */
class TaskUserConfig extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'tasks_per_week', 'family_tasks_completed', 'family_id', 'color',
        'total_user_tasks'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'completedFamilyTasks' => Task::class
    ];

    public static function createNewEntity(User $user, Family $family): TaskUserConfig
    {
        $config = new TaskUserConfig([
            'tasks_per_week' => 5,
            'color' => '#994455'
        ]);
        $config->family()->associate($family);
        $config->user()->associate($user);
        $config->save();

        return $config;
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param TaskUserConfig $entity
     * @param $request
     * @return Model|TaskUserConfig
     */
    public static function updateEntity(Model $entity, $request): Model|TaskUserConfig
    {
        $entity->tasks_per_week = $request['tasksPerWeek'];
        $entity->color = $request['color'];
        $entity->save();

        return $entity;
    }

    public function getCompletedFamilyTasksAttribute()
    {
        $startOfWeek = Carbon::today()->setTimezone('America/Los_Angeles')->startOfWeek();

        return Task::query()
                   ->whereNotNull('completed_at')
                   ->where('completed_by_id', '=', $this->user_id)
                   ->where('completed_at', '>', $startOfWeek)
                   ->where('owner_type', '=', Family::class)
                   ->where('owner_id', '=', $this->family_id)
                   ->get();
    }

    public function getTotalUserTasksAttribute(): int
    {
        return Task::query()
                   ->where('owner_type', '=', User::class)
                   ->where('owner_id', '=', $this->id)
                   ->whereNull('completed_at')
                   ->whereNull('cleared_at')
                   ->count();
    }
}
