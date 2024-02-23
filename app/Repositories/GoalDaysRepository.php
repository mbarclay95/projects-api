<?php

namespace App\Repositories;

use App\Models\Goals\GoalDay;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GoalDaysRepository extends DefaultRepository
{
    public function createEntity($request, Authenticatable $user): Model|array
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
     * @param Authenticatable $user
     * @return Model|array
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->amount = $request['amount'];
        $model->save();

        return $model;
    }
}
