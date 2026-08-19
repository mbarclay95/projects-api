<?php

namespace App\Repositories\Backups;

use App\Models\Backups\BackupStepJob;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class BackupStepJobsRepository extends DefaultRepository
{
    /**
     * @return BackupStepJob|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $backupJobStep = new BackupStepJob([
            'sort' => $request['sort'],
        ]);
        $backupJobStep->user()->associate($user);
        $backupJobStep->backupStep()->associate($request['backupStepId']);
        $backupJobStep->backupJob()->associate($request['backupJobId']);
        $backupJobStep->save();

        return $backupJobStep;
    }
}
