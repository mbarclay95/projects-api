<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

/**
 * Class Family
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 *
 * @property Collection|TaskUserConfig[] userConfigs
 * @property Collection|User[] members
 */
class Family extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [
        'members' => User::class
    ];

    public static function getUserEntity(int $entityId, int $authId)
    {
        return Family::query()
                     ->whereExists(function ($whereIn) use ($entityId, $authId) {
                         $whereIn->select(new Expression('1'))
                                 ->from('task_user_configs')
                                 ->where('family_id', '=', $entityId)
                                 ->where('user_id', '=', $authId);
                     })
                     ->first();
    }

    public static function createEntity($request, int $authId): Family
    {
        $family = new Family([
            'name' => $request['name']
        ]);
        $family->save();

        return $family;
    }

    public static function updateEntity(Model $entity, $request): Model
    {
        clock($request);
        $entity->name = $request['name'];
        $members = User::query()
                       ->whereIn('id', Collection::make($request['members'])->map(function ($user) {
                           return $user['id'];
                       }))
                       ->get();
//        $user->syncRoles($roles);
        $entity->save();
        foreach ($members as $member) {
            $config = new TaskUserConfig([
                'tasks_per_week' => 5
            ]);
            $config->family()->associate($entity);
            $config->user()->associate($member);
            $config->save();
        }
        return $entity;
    }

    public function userConfigs(): HasMany
    {
        return $this->hasMany(TaskUserConfig::class);
    }

    public function members(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, TaskUserConfig::class, 'user_id', 'id');
    }
}
