<?php

namespace App\Repositories\UserGroups;

use App\Enums\FeatureEnum;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class UserGroupsRepository extends DefaultRepository
{
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $userGroup = new UserGroup([
            'name' => $request['name'],
            'config' => FeatureEnum::TASKS->buildConfig($request, []),
            'scope' => 'tasks',
        ]);
        $members = User::query()
            ->whereIn('id', Collection::make($request['members'])->map(function ($user) {
                return $user['id'];
            }))
            ->get();
        $userGroup->save();
        $userGroup->syncMembers($members);
        $userGroup->refresh();

        return $userGroup;
    }

    /**
     * @param  UserGroup  $model
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->name = $request['name'];
        $model->config = FeatureEnum::TASKS->buildConfig($request, $model->config);
        $members = User::query()
            ->whereIn('id', Collection::make($request['members'])->map(function ($user) {
                return $user['id'];
            }))
            ->get();
        $model->syncMembers($members);
        $model->save();
        $model->refresh();

        return $model;
    }
}
