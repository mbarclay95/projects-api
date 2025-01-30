<?php

namespace App\Http\Controllers\Backups;

use App\Models\Backups\Backup;
use App\Services\Backups\RunBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mbarclay36\LaravelCrud\CrudController;
use Symfony\Component\HttpFoundation\JsonResponse;

class BackupController extends CrudController
{
    protected static string $modelClass = Backup::class;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
        'backupSteps' => 'required|array',
    ];
    protected static array $updateRules = [];

    public function manualBackupRun(Request $request, int $backupId): JsonResponse
    {
        $userId = Auth::id();

        /** @var Backup $backup */
        $backup = Backup::query()
                        ->where('user_id', '=', $userId)
                        ->find($backupId);
        if (!$backup) {
            abort(404, 'Backup not found with the given backup_id');
        }

        (new RunBackupService($backup))->run();

        return new JsonResponse(['success' => true]);
    }
}
