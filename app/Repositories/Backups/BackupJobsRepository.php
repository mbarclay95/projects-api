<?php

namespace App\Repositories\Backups;

use App\Models\Backups\Backup;
use App\Models\Backups\BackupJob;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class BackupJobsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return BackupJob|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        /** @var Backup $backup */
        $backup = $request['backup'];

        $backupJob = new BackupJob();
        $backupJob->user()->associate($user);
        $backupJob->backup()->associate($backup->id);
        $backupJob->schedule()->associate($request['scheduleId'] ?? null);
        $backupJob->save();

        foreach ($backup->backupSteps as $backupStep) {
            BackupStepJobsRepository::createEntityStatic([
                'backupStepId' => $backupStep->id,
                'backupJobId' => $backupJob->id,
                'sort' => $backupStep->sort
            ], $user);
        }

        return $backupJob;
    }
}
