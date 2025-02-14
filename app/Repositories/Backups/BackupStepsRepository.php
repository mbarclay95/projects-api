<?php

namespace App\Repositories\Backups;

use App\Models\Backups\BackupStep;
use App\Models\Backups\BackupStepJob;
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
        $backupStep->save();

        return $backupStep;
    }

    /**
     * @param BackupStep $model
     * @param $request
     * @param Authenticatable $user
     * @return BackupStep|array
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->name = $request['name'];
        $model->sort = $request['sort'];
        $model->backup_step_type = $request['backupStepType'];
        $model->config = $request['config'];
        $model->save();

        return $model;
    }
}
