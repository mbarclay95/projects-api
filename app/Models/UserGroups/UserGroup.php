<?php

namespace App\Models\UserGroups;

use App\Enums\FamilyTaskStrategyEnum;
use App\Enums\FeatureEnum;
use App\Models\ApiModels\UserGroupMemberApiModel;
use App\Models\Tasks\{RecurringTask, Task, TaskUserConfig};
use App\Models\Users\User;
use App\Repositories\Tasks\TaskUserConfigsRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class UserGroup
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property string scope
 * @property array config
 * @property Collection|TaskUserConfig[] userConfigs
 * @property Collection|User[] members
 */
class UserGroup extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'scope', 'tasks_per_week', 'total_family_tasks',
        'task_strategy', 'task_points', 'min_week_offset', 'min_year'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'members' => UserGroupMemberApiModel::class,
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public static function toApiModel(?Model $model, array $hideItem = []): array|null|string
    {
        if ($model && $model->scope !== FeatureEnum::TASKS->value) {
            $hideItem = array_merge($hideItem, [
                'tasks_per_week', 'total_family_tasks', 'task_strategy', 'task_points', 'min_week_offset', 'min_year',
            ]);
        }

        return parent::toApiModel($model, $hideItem);
    }

    /**
     * @param  User[]|Collection  $newMembers
     */
    public function syncMembers($newMembers): void
    {
        $today = Carbon::now('America/Los_Angeles')->toDateString();
        $isTasksScope = $this->scope === FeatureEnum::TASKS->value;
        foreach ($this->members as $member) {
            if ($newMembers->doesntContain('id', $member->id)) {
                $this->members()->detach($member->id);
                if ($isTasksScope) {
                    TaskUserConfig::query()
                        ->where('user_group_id', '=', $this->id)
                        ->where('user_id', '=', $member->id)
                        ->where('start_date', '>=', $today)
                        ->delete();
                    TaskUserConfig::query()
                        ->where('user_group_id', '=', $this->id)
                        ->where('user_id', '=', $member->id)
                        ->where('end_date', '>=', $today)
                        ->update(['end_date' => Carbon::parse($today)->subDay()->toDateString()]);
                }
            }
        }
        /** @var User $newMember */
        foreach ($newMembers as $newMember) {
            if ($this->members->doesntContain('id', $newMember->id)) {
                $this->members()->attach($newMember->id, ['scope' => $this->scope]);
                if ($isTasksScope) {
                    TaskUserConfigsRepository::createEntityStatic(['userGroup' => $this, 'user' => $newMember], $newMember);
                }
            }
        }
    }

    public function getTasksPerWeekAttribute(): float|int
    {
        $dayCountQuery = RecurringTask::query();
        if ($this->task_strategy == FamilyTaskStrategyEnum::PER_TASK) {
            $dayCountQuery->selectRaw("sum(case
when frequency_unit = 'week'
then 1.0 / (frequency_amount * 7.0)
when frequency_unit = 'month'
then 1.0 / (frequency_amount * 30.437)
when frequency_unit = 'year'
then 1.0 / (frequency_amount * 365.25)
else frequency_amount
end)");
        } else {
            $dayCountQuery->selectRaw("sum(case
when frequency_unit = 'week'
then task_point / (frequency_amount * 7.0)
when frequency_unit = 'month'
then task_point / (frequency_amount * 30.437)
when frequency_unit = 'year'
then task_point / (frequency_amount * 365.25)
else task_point / (frequency_amount * 1.0)
end)");
        }

        $dayCount = $dayCountQuery->where('owner_type', '=', (new UserGroup)->getMorphClass())
            ->where('owner_id', '=', $this->id)
            ->where('is_active', '=', true)
            ->first();
        $weekCount = $dayCount['sum'] * 7;

        return $weekCount / (count($this->members) == 0 ? 1 : count($this->members));
    }

    public function getTotalFamilyTasksAttribute(): int
    {
        return Task::query()
            ->where('owner_type', '=', (new UserGroup)->getMorphClass())
            ->where('owner_id', '=', $this->id)
            ->whereNull('completed_at')
            ->whereNull('cleared_at')
            ->count();
    }

    public function userConfigs(): HasMany
    {
        $date = Carbon::now('America/Los_Angeles')->toDateString();

        return $this->hasMany(TaskUserConfig::class)
            ->where('task_user_configs.start_date', '<=', $date)
            ->where('task_user_configs.end_date', '>=', $date);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_group_user')->withTimestamps()->orderBy('users.id');
    }

    public function getTaskStrategyAttribute(): ?FamilyTaskStrategyEnum
    {
        $taskStrategy = $this->config['task_strategy'] ?? null;

        return $taskStrategy ? FamilyTaskStrategyEnum::from($taskStrategy) : null;
    }

    public function getTaskPointsAttribute(): array
    {
        $taskPoints = $this->config['task_points'] ?? [];
        sort($taskPoints);

        return $taskPoints;
    }

    public function getMinWeekOffsetAttribute(): int
    {
        /** @var TaskUserConfig $config */
        $config = TaskUserConfig::query()
            ->where('user_group_id', '=', $this->id)
            ->whereIn('user_id', $this->members->pluck('id'))
            ->orderBy('start_date')
            ->first();

        if (! $config) {
            $this->setAttribute('min_year', Carbon::today()->year);

            return 0;
        }

        $this->setAttribute('min_year', Carbon::parse($config->start_date)->year);

        return Carbon::now('America/Los_Angeles')->diffInWeeks($config->start_date, false);
    }
}
