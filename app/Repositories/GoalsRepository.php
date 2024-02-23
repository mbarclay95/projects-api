<?php

namespace App\Repositories;

use App\Models\Goals\Goal;
use App\Models\Goals\GoalDay;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GoalsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @param bool $viewOnlyForUser
     * @return Collection|array
     */
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        /** @var Goal[] $goals */
        $goals = Goal::query()
                     ->with(['goalDays' => function ($query) use ($request) {
                         $query->filter($request);
                     }])
                     ->where('user_id', '=', $user->id)
                     ->filter($request)
                     ->get();

        $date = Carbon::now('America/Los_Angeles')->addWeeks($request['weekOffset']);
        foreach ($goals as $goal) {
            $goal->getCurrentAmount($date);
        }

        return $goals;
    }

    /**
     * @param $request
     * @param Authenticatable $user
     * @return Model|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $goal = new Goal([
            'title' => $request['title'],
            'verb' => $request['verb'],
            'equality' => $request['equality'],
            'expected_amount' => $request['expectedAmount'],
            'unit' => $request['unit'],
            'length_of_time' => $request['lengthOfTime'],
        ]);
        $goal->user()->associate($user);
        $goal->save();
        $goal->current_amount = 0;

        return $goal;
    }

    /**
     * @param Goal $model
     * @param $request
     * @param Authenticatable $user
     * @return Model|array
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->title = $request['title'];
        $model->verb = $request['verb'];
        $model->equality = $request['equality'];
        $model->expected_amount = $request['expectedAmount'];
        $model->unit = $request['unit'];
        $model->length_of_time = $request['lengthOfTime'];
        $model->save();

        $date = Carbon::now('America/Los_Angeles')->addWeeks($request['weekOffset']);
        $model->getCurrentAmount($date);

        return $model;
    }

    /**
     * @param Goal $model
     * @param Authenticatable $user
     * @return bool
     */
    public function destroyEntity(Model $model, Authenticatable $user): bool
    {
        $model->delete();

        GoalDay::query()
               ->where('goal_id', '=', $model->id)
               ->delete();

        return true;
    }
}
