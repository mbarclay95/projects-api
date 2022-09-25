<?php

namespace App\Models\Tasks;

use App\Enums\FamilyTaskStrategyEnum;
use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;

/**
 * Class Family
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property string color
 * @property string task_strategy
 *
 * @property Collection|TaskUserConfig[] userConfigs
 * @property Collection|User[] members
 * @property Collection|TaskPoint[] taskPoints
 */
class Family extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'color', 'tasks_per_week', 'total_family_tasks',
        'task_strategy'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'members' => User::class,
        'taskPoints' => TaskPoint::class
    ];

    protected $casts = [
        'task_strategy' => FamilyTaskStrategyEnum::class
    ];

    public static function getEntities($request, User $auth, bool $viewAnyForUser)
    {
        return Family::query()
                     ->with('members.roles', 'members.permissions', 'members.userConfig', 'members.taskUserConfig')
                     ->get();
    }

    public static function getEntity(int $entityId, User $auth, bool $viewForUser)
    {
        return Family::query()->find($entityId);
//        return Family::query()
//                     ->with('members.roles', 'members.permissions', 'members.userConfig', 'members.taskUserConfig')
//                     ->whereExists(function ($whereIn) use ($entityId, $auth) {
//                         $whereIn->select(new Expression('1'))
//                                 ->from('task_user_configs')
//                                 ->where('family_id', '=', $entityId)
//                                 ->where('user_id', '=', $auth->id);
//                     })
//                     ->first();
    }

    public static function createEntity($request, User $auth): Family
    {
        $family = new Family([
            'name' => $request['name'],
            'color' => $request['color'],
            'task_strategy' => $request['taskStrategy']
        ]);
        $members = User::query()
                       ->whereIn('id', Collection::make($request['members'])->map(function ($user) {
                           return $user['id'];
                       }))
                       ->get();
        $family->save();
        $family->syncMembers($members);
        $family->refresh();

        return $family;
    }

    /**
     * @param array|Collection $newMembers
     * @return void
     */
    public function syncMembers($newMembers): void
    {
        foreach ($this->members as $member) {
            if ($newMembers->doesntContain('id', $member->id)) {
                $member->taskUserConfig()->delete();
            }
        }
        /** @var User $newMember */
        foreach ($newMembers as $newMember) {
            if ($this->members->doesntContain('id', $newMember->id)) {
                TaskUserConfig::createNewEntity($newMember, $this);
            }
        }
    }

    /**
     * @param Family $entity
     * @param $request
     * @param User $auth
     * @return Model
     */
    public static function updateEntity(Model $entity, $request, User $auth): Model
    {
        $entity->name = $request['name'];
        $entity->color = $request['color'];
        $entity->task_strategy = $request['taskStrategy'];
        $members = User::query()
                       ->whereIn('id', Collection::make($request['members'])->map(function ($user) {
                           return $user['id'];
                       }))
                       ->get();
        $entity->syncMembers($members);
        $entity->save();
        $entity->refresh();

        return $entity;
    }

    public function getTasksPerWeekAttribute(): float|int
    {
        $dayCountQuery = RecurringTask::query();
        if ($this->task_strategy == FamilyTaskStrategyEnum::PER_TASK) {
            $dayCountQuery->selectRaw("sum(case
when frequency_unit = 'week'
then 1.0 / (frequency_amount * 7.0)
when frequency_unit = 'month'
then 1.0 / (frequency_amount * 30.0)
else frequency_amount
end)");
        } else {
            $dayCountQuery->join('task_points', 'recurring_tasks.task_point_id', '=', 'task_points.id')
                          ->selectRaw("sum(case
when frequency_unit = 'week'
then points / (frequency_amount * 7.0)
when frequency_unit = 'month'
then points / (frequency_amount * 30.0)
else frequency_amount
end)");
        }

        $dayCount = $dayCountQuery->where('owner_type', '=', Family::class)
                                  ->where('owner_id', '=', $this->id)
                                  ->first();
        $weekCount = $dayCount['sum'] * 7;

        return $weekCount / count($this->members);
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
        return $this->hasMany(TaskUserConfig::class);
    }

    public function taskPoints(): HasMany
    {
        return $this->hasMany(TaskPoint::class);
    }

    public function members(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, TaskUserConfig::class, 'family_id', 'id', 'id', 'user_id');
    }
}
