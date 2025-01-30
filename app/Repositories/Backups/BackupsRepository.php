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
            $backupStep['scheduledBackupId'] = $request['scheduledBackupId'] ?? null;
            BackupStepsRepository::createEntityStatic($backupStep, $user);
        }

        return $backup;
    }
}
