<?php

namespace App\Repositories\UserGroups;

use App\Enums\FeatureEnum;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Mbarclay36\LaravelCrud\DefaultRepository;

class UserGroupsRepository extends DefaultRepository
{
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $scope = FeatureEnum::from($request['scope']);
        Validator::make($request, $scope->groupConfigRules())->validate();

        $userGroup = new UserGroup([
            'name' => $request['name'],
            'config' => $scope->buildConfig($request, []),
            'scope' => $scope->value,
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
        $scope = FeatureEnum::from($model->scope);
        Validator::make($request, $scope->groupConfigRules())->validate();

        $model->name = $request['name'];
        $model->config = $scope->buildConfig($request, $model->config);
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
