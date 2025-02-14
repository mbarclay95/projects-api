<?php

namespace App\Repositories\Backups;

use App\Models\Backups\Backup;
use App\Models\Backups\BackupStep;
use App\Models\Backups\BackupStepJob;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class BackupStepJobsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return BackupStepJob|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $backupJobStep = new BackupStepJob([
            'sort' => $request['sort']
        ]);
        $backupJobStep->user()->associate($user);
        $backupJobStep->backupStep()->associate($request['backupStepId']);
        $backupJobStep->backupJob()->associate($request['backupJobId']);
        $backupJobStep->save();

        return $backupJobStep;
    }
}
