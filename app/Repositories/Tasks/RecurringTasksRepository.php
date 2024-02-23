<?php

namespace App\Repositories\Tasks;

use App\Models\Tasks\Family;
use App\Models\Tasks\RecurringTask;
use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class RecurringTasksRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return Model|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $task = new RecurringTask([
            'name' => $request['name'],
            'description' => $request['description'],
            'owner_type' => $request['ownerType'] === 'family' ? Family::class : User::class,
            'owner_id' => $request['ownerType'] === 'family' ? $user->family->id : $user->id,
            'frequency_amount' => $request['frequencyAmount'],
            'frequency_unit' => $request['frequencyUnit'],
            'is_active' => true,
            'priority' => $request['priority'],
        ]);
        if (isset($request['taskPoint'])) {
            $task->task_point = $request['taskPoint'];
        }
        $task->save();

        return $task;
    }
}
