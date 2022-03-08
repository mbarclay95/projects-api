<?php

namespace App\Http\Controllers\Goals;

use App\Http\Controllers\Controller;
use App\Models\Goals\Goal;
use App\Models\Goals\GoalDay;
use Illuminate\Http\Request;

class GoalDayController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Goal $goal)
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Goal $goal, Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Goals\GoalDay  $goalDay
     * @return \Illuminate\Http\Response
     */
    public function show(Goal $goal, GoalDay $goalDay)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Goals\GoalDay  $goalDay
     * @return \Illuminate\Http\Response
     */
    public function update(Goal $goal, Request $request, GoalDay $goalDay)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Goals\GoalDay  $goalDay
     * @return \Illuminate\Http\Response
     */
    public function destroy(Goal $goal, GoalDay $goalDay)
    {
        //
    }
}
