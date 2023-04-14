<?php

namespace App\Models\Tasks;

use App\Models\User;
use App\Repositories\Tasks\TaskUserConfigsRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mbarclay36\LaravelCrud\ApiModel;
use Mbarclay36\LaravelCrud\Traits\HasRepository;

/**
 * Class Family
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property integer tasks_per_week
 * @property Carbon start_date
 * @property Carbon end_date
 *
 * @property integer family_id
 * @property Family family
 *
 * @property integer user_id
 * @property User user
 */
class TaskUserConfig extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'user_id', 'user_name', 'tasks_per_week', 'family_id'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'completedFamilyTasks' => Task::class
    ];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getCompletedFamilyTasks(Carbon $date)
    {
        $startDate = $date->clone()->startOfWeek()->setTimezone('UTC');
        $endDate = $date->clone()->endOfWeek()->setTimezone('UTC');

        return Task::query()
                   ->whereNotNull('completed_at')
                   ->where('completed_by_id', '=', $this->user_id)
                   ->where('completed_at', '>', $startDate)
                   ->where('completed_at', '<', $endDate)
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

    public function getUserNameAttribute(): string
    {
        return $this->user->name;
    }
}
