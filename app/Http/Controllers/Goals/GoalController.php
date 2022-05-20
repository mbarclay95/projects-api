<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Http\Requests\Goals\GoalStoreRequest;
use App\Models\Goals\Goal;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GoalController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Goal::class, 'goal');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        /** @var Goal[] $goals */
        $goals = Goal::query()
                     ->where('user_id', '=', $userId)
                     ->get();

        return new JsonResponse(Goal::toApiModels($goals));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param GoalStoreRequest $request
     * @return JsonResponse
     */
    public function store(GoalStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = Auth::id();

        $goal = new Goal([
            'title' => $validated['title'],
            'verb' => $validated['verb'],
            'equality' => $validated['equality'],
            'expected_amount' => $validated['expectedAmount'],
            'unit' => $validated['unit'],
            'length_of_time' => $validated['lengthOfTime'],
        ]);
        $goal->user()->associate($userId);
        $goal->save();

        return new JsonResponse(Goal::toApiModel($goal));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Goals\Goal $goal
     * @return \Illuminate\Http\Response
     */
    public function show(Goal $goal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Goals\Goal $goal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Goal $goal)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Goals\Goal $goal
     * @return \Illuminate\Http\Response
     */
    public function destroy(Goal $goal)
    {
        //
    }
}
