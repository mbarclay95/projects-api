<?php

namespace App\Repositories;

use App\Models\Goals\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GoalsRepository extends DefaultRepository
{
    protected static string|null $modelClass = Goal::class;

    public function getEntities($request, User $user, bool $viewOnlyForUser): Collection|array
    {
        return Goal::query()
                   ->with(['goalDays' => function ($query) use ($request) {
                       $query->filter($request);
                   }])
                   ->where('user_id', '=', $user->id)
                   ->filter($request)
                   ->get();
    }

    public function createEntity($request, User $user): Model|array
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

        return $goal;
    }
}
