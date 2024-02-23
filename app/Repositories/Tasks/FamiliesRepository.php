<?php

namespace App\Repositories\Tasks;

use App\Models\Tasks\Family;
use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class FamiliesRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return Model|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $family = new Family([
            'name' => $request['name'],
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
     * @param Family $model
     * @param $request
     * @param Authenticatable $user
     * @return Model|array
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->name = $request['name'];
        $model->task_strategy = $request['taskStrategy'];
        if (array_key_exists('taskPoints', $request)) {
            $model->task_points = [
                'points' => $request['taskPoints']
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
