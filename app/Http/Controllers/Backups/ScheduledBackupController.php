<?php

namespace App\Http\Controllers\Backups;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backups\ScheduledBackupStoreRequest;
use App\Models\Backups\ScheduledBackup;
use App\Models\Backups\ScheduledBackupStep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScheduledBackupController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ScheduledBackup::class, 'scheduled-backup');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        /** @var ScheduledBackup[] $scheduledBackups */
        $scheduledBackups = ScheduledBackup::query()
                                           ->where('user_id', '=', $userId)
                                           ->get();

        return new JsonResponse(ScheduledBackup::toApiModels($scheduledBackups));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ScheduledBackupStoreRequest $request
     * @return JsonResponse
     */
    public function store(ScheduledBackupStoreRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $userId = Auth::id();

        $scheduledBackup = ScheduledBackup::createFromRequest($validated, $userId);
        foreach ($validated['scheduledBackupSteps'] as $step) {
            ScheduledBackupStep::createFromRequest($step, $userId, $scheduledBackup->id);
        }

        return new JsonResponse(ScheduledBackup::toApiModel($scheduledBackup));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Backups\ScheduledBackup $scheduledBackup
     * @return \Illuminate\Http\Response
     */
    public function show(ScheduledBackup $scheduledBackup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param \App\Models\Backups\ScheduledBackup $scheduledBackup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ScheduledBackup $scheduledBackup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Backups\ScheduledBackup $scheduledBackup
     * @return \Illuminate\Http\Response
     */
    public function destroy(ScheduledBackup $scheduledBackup)
    {
        //
    }
}
