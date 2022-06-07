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

    public static function getUserEntity(int $entityId, User $auth)
    {
        return Family::query()
                     ->whereExists(function ($whereIn) use ($entityId, $auth) {
                         $whereIn->select(new Expression('1'))
                                 ->from('task_user_configs')
                                 ->where('family_id', '=', $entityId)
                                 ->where('user_id', '=', $auth->id);
                     })
                     ->first();
    }

    public static function createEntity($request, User $auth): Family
    {
        $family = new Family([
            'name' => $request['name']
        ]);
        $family->save();

        return $family;
    }

    /**
     * @param Family $entity
     * @param $request
     * @return Model
     */
    public static function updateEntity(Model $entity, $request): Model
    {
        $entity->name = $request['name'];
        $members = User::query()
                       ->whereIn('id', Collection::make($request['members'])->map(function ($user) {
                           return $user['id'];
                       }))
                       ->get();
        $entity->syncMembers($members);
        $entity->save();

        return $entity;
    }

    public function userConfigs(): HasMany
    {
        return $this->hasMany(TaskUserConfig::class);
    }

    public function members(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, TaskUserConfig::class, 'family_id', 'id', 'id', 'user_id');
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

        $this->members()->load();
    }
}
