<?php

namespace App\Repositories\UserGroups;

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
            'task_strategy' => $request['taskStrategy'],
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
        $model->task_strategy = $request['taskStrategy'];
        if (array_key_exists('taskPoints', $request)) {
            $model->task_points = [
                'points' => $request['taskPoints'],
            ];
        }
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
