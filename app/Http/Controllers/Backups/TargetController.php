<?php

namespace App\Http\Controllers\Backups;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backups\TargetStoreRequest;
use App\Models\Backups\Target;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TargetController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Target::class, 'target');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        /** @var Target[] $targets */
        $targets = Target::query()->get();

        return new JsonResponse(Target::toApiModels($targets));
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

        return new JsonResponse(Target::toApiModel($target));
    }

    /**
     * Display the specified resource.
     *
     * @param Target $target
     * @return Response
     */
    public function show(Target $target)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param TargetStoreRequest $request
     * @param Target $target
     * @return JsonResponse
     */
    public function update(TargetStoreRequest $request, Target $target): JsonResponse
    {
        $validated = $request->validated();

        $target->name = $validated['name'];
        $target->target_url = $validated['targetUrl'];
        $target->host_name = $validated['hostName'];
        $target->save();

        return new JsonResponse(Target::toApiModel($target));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Target $target
     * @return Response
     */
    public function destroy(Target $target)
    {
        //
    }
}
