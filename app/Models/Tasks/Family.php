<?php

namespace App\Models\Tasks;

use App\Enums\FamilyTaskStrategyEnum;
use App\Models\ApiModels\FamilyMemberApiModel;
use App\Models\Users\User;
use App\Repositories\Tasks\TaskUserConfigsRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * Class Family
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property string task_strategy
 * @property array task_points
 *
 * @property Collection|TaskUserConfig[] userConfigs
 * @property Collection|User[] members
 */
class Family extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'tasks_per_week', 'total_family_tasks',
        'task_strategy', 'task_points', 'min_week_offset', 'min_year'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'members' => FamilyMemberApiModel::class,
    ];

    protected $casts = [
        'task_strategy' => FamilyTaskStrategyEnum::class,
        'task_points' => 'array'
    ];

    /**
     * @param User[]|Collection $newMembers
     * @return void
     */
    public function syncMembers($newMembers): void
    {
        foreach ($this->members as $member) {
            if ($newMembers->doesntContain('id', $member->id)) {
                $member->taskUserConfig()->where('family_id', '=', $this->id)->delete();
            }
        }
        /** @var User $newMember */
        foreach ($newMembers as $newMember) {
            if ($this->members->doesntContain('id', $newMember->id)) {
                TaskUserConfigsRepository::createEntityStatic(['family' => $this, 'user' => $newMember,'tasksPerWeek' => 5], $newMember);
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

        $dayCount = $dayCountQuery->where('owner_type', '=', Family::class)
                                  ->where('owner_id', '=', $this->id)
                                  ->where('is_active', '=', true)
                                  ->first();
        $weekCount = $dayCount['sum'] * 7;

        return $weekCount / (count($this->members) == 0 ? 1 : count($this->members));
    }

    public function getTotalFamilyTasksAttribute(): int
    {
        return Task::query()
                   ->where('owner_type', '=', Family::class)
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

    public function members(): HasManyThrough
    {
        $date = Carbon::now('America/Los_Angeles')->toDateString();
        return $this->hasManyThrough(User::class, TaskUserConfig::class, 'family_id', 'id', 'id', 'user_id')
                    ->where('task_user_configs.start_date', '<=', $date)
                    ->where('task_user_configs.end_date', '>=', $date);
    }

    public function getTaskPointsAttribute($value): array
    {
        if ($value) {
            $valueArray = json_decode($value, true);
            if (array_key_exists('points', $valueArray)) {
                $numbers = $valueArray['points'];
                sort($numbers);
                return $numbers;
            }
        }

        return [];
    }

    public function getMinWeekOffsetAttribute(): int
    {
        /** @var TaskUserConfig $config */
        $config = TaskUserConfig::query()
                                ->where('family_id', '=', $this->id)
                                ->whereIn('user_id', $this->members->pluck('id'))
                                ->orderBy('start_date')
                                ->first();

        if (!$config) {
            $this->setAttribute('min_year', Carbon::today()->year);
            return 0;
        }

        $this->setAttribute('min_year', Carbon::parse($config->start_date)->year);

        return Carbon::now('America/Los_Angeles')->diffInWeeks($config->start_date, false);
    }
}
