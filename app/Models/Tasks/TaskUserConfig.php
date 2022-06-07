<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Class Family
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property integer tasks_per_week
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

    protected static array $apiModelAttributes = ['tasks_per_week', 'tasks_completed', 'family_id'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    public static function getTasksCompletedAttribute(): int
    {
        $authId = Auth::id();
        $startOfWeek = Carbon::today()->startOfWeek();

        return Task::query()
                   ->whereNotNull('completed_at')
                   ->where('completed_by_id', '=', $authId)
                   ->where('completed_at', '>', $startOfWeek)
                   ->count();
    }

    public static function createNewEntity(User $user, Family $family): TaskUserConfig
    {
        $config = new TaskUserConfig([
            'tasks_per_week' => 5
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
}
