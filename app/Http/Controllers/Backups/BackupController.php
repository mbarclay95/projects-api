<?php

namespace App\Http\Controllers\Backups;

use App\Http\Requests\Backups\BackupStoreRequest;
use App\Models\Backups\Backup;
use App\Models\Backups\BackupStep;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mbarclay36\LaravelCrud\CrudController;

class BackupController extends CrudController
{
    protected static string $modelClass = Backup::class;

    public function __construct()
    {
        $this->authorizeResource(Backup::class, 'backup');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BackupStoreRequest $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();
        $validated = $request->validated();
        $backupSteps = $validated['backupSteps'];

        $backup = Backup::create($validated['name'], $userId);
        foreach ($backupSteps as $backupStep) {
            BackupStep::createFromRequest($backupStep, $userId, $backup->id);
        }

        return new JsonResponse(Backup::toApiModel($backup));
    }

}
