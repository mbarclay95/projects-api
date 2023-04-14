<?php

namespace App\Repositories;

use App\Models\Goals\Goal;
use App\Models\Goals\GoalDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GoalDaysRepository extends DefaultRepository
{
    public function createEntity($request, User $user): Model|array
    {
        $goalDay = new GoalDay([
            'amount' => $request['amount'],
            'date' => Carbon::parse($request['date'])->setTimezone('America/Los_Angeles')->startOfDay()->toDateString(),
        ]);
        $goalDay->user()->associate($user);
        $goalDay->goal()->associate($request['goalId']);
        $goalDay->save();

        return $goalDay;
    }

    /**
     * @param GoalDay $model
     * @param $request
     * @param User $user
     * @return Model|array
     */
    public function updateEntity(Model $model, $request, User $user): Model|array
    {
        $model->amount = $request['amount'];
        $model->save();

        return $model;
    }
}
