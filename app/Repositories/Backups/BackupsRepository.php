<?php

namespace App\Repositories\Backups;

use App\Models\Backups\Backup;
use App\Models\Backups\BackupStep;
use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class BackupsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param User $user
     * @param bool $viewOnlyForUser
     * @return Collection|Backup[]
     */
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        return Backup::query()
                     ->with('backupSteps', 'backupJobs.backupStepJobs', 'schedules')
                     ->where('user_id', '=', $user->id)
                     ->orderBy('created_at')
                     ->get();
    }

    /**
     * @param $request
     * @param Authenticatable $user
     * @return Backup|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $backup = new Backup([
            'name' => $request['name'],
        ]);
        $backup->user()->associate($user);
        $backup->save();

        foreach ($request['backupSteps'] as $backupStep) {
            $backupStep['backupId'] = $backup->id;
            BackupStepsRepository::createEntityStatic($backupStep, $user);
        }

        return $backup;
    }

    /**
     * @param Backup $model
     * @param $request
     * @param Authenticatable $user
     * @return Backup|array
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->name = $request['name'];
        $model->save();

        $model->load('backupSteps');
        $requestCollection = Collection::make($request['backupSteps']);
        foreach ($model->backupSteps as $backupStep) {
            if ($requestCollection->doesntContain(function ($backupStepRequest) use ($backupStep) {
                return $backupStepRequest['id'] == $backupStep->id;
            })) {
                BackupStepsRepository::destroyEntityStatic($backupStep, $user);
            }
        }
        foreach ($request['backupSteps'] as $backupStepRequest) {
            // using negative ids in the FE because I need ids to edit
            if ($backupStepRequest['id'] < 0) {
                $backupStepRequest['backupId'] = $model->id;
                BackupStepsRepository::createEntityStatic($backupStepRequest, $user);
            } else {
                /** @var BackupStep $backupStep */
                $backupStep = $model->backupSteps->find($backupStepRequest['id']);
                BackupStepsRepository::updateEntityStatic($backupStep, $backupStepRequest, $user);
            }
        }
        $model->load('backupSteps', 'backupJobs.backupStepJobs', 'schedules');

        return $model;
    }

    /**
     * @param Backup $model
     * @param Authenticatable $user
     * @return bool
     */
    public function destroyEntity(Model $model, Authenticatable $user): bool
    {
        $model->delete();
        $model->backupSteps->each(function (BackupStep $backupStep) use ($user) {
            BackupStepsRepository::destroyEntityStatic($backupStep, $user);
        });

        return true;
    }
}
