<?php

namespace App\Http\Controllers\Backups;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backups\BackupStoreRequest;
use App\Models\ApiModels\Backups\BackupApiModel;
use App\Models\Backups\Backup;
use App\Models\Backups\BackupStep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BackupController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Backup::class, 'backup');
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();
        /** @var Backup[] $backups */
        $backups = Backup::query()
                         ->where('user_id', '=', $userId)
                         ->with('backupSteps')
                         ->get();

        return new JsonResponse(BackupApiModel::fromEntities($backups));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BackupStoreRequest $request
     * @return JsonResponse
     */
    public function store(BackupStoreRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $validated = $request->validated();
        $backupSteps = $validated['backupSteps'];

        $backup = Backup::create($validated['name'], $userId);
        foreach ($backupSteps as $backupStep) {
            BackupStep::createFromRequest($backupStep, $userId, $backup->id);
        }

        return new JsonResponse(BackupApiModel::fromEntity($backup));
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Backups\Backup $backup
     * @return \Illuminate\Http\Response
     */
    public function show(Backup $backup)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Backups\Backup $backup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Backup $backup)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Backups\Backup $backup
     * @return \Illuminate\Http\Response
     */
    public function destroy(Backup $backup)
    {
        //
    }
}
