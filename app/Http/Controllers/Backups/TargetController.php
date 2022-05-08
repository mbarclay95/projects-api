<?php

namespace App\Http\Controllers\Backups;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backups\TargetStoreRequest;
use App\Models\ApiModels\Backups\TargetApiModel;
use App\Models\Backups\Target;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TargetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        /** @var Target[] $targets */
        $targets = Target::query()->get();

        return new JsonResponse(TargetApiModel::fromEntities($targets));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param TargetStoreRequest $request
     * @return JsonResponse
     */
    public function store(TargetStoreRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $validated = $request->validated();

        $target = new Target([
            "name" => $validated['name'],
            "target_url" => $validated['targetUrl'],
            "host_name" => $validated['hostName'],
        ]);
        $target->user()->associate($userId);
        $target->save();

        return new JsonResponse(TargetApiModel::fromEntity($target));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Backups\Target  $target
     * @return \Illuminate\Http\Response
     */
    public function show(Target $target)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  \App\Models\Backups\Target  $target
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Target $target)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Backups\Target  $target
     * @return \Illuminate\Http\Response
     */
    public function destroy(Target $target)
    {
        //
    }
}
