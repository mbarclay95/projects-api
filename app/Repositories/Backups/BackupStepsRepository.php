<?php

namespace App\Repositories\Backups;

use App\Models\Backups\BackupStep;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class BackupStepsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return BackupStep|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $backupStep = new BackupStep([
            'name' => $request['name'],
            'sort' => $request['sort'],
            'backup_step_type' => $request['backupStepType'],
            'config' => $request['config'],
        ]);
        $backupStep->user()->associate($user);
        $backupStep->backup()->associate($request['backupId']);
        $backupStep->scheduledBackup()->associate($request['scheduledBackupId']);
        $backupStep->save();

        return $backupStep;
    }
}
