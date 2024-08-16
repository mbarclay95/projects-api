<?php

namespace App\Repositories\Backups;

use App\Models\Backups\Backup;
use App\Models\Backups\BackupStep;
use App\Models\Backups\Target;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class TargetsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @param bool $viewOnlyForUser
     * @return Collection|Target[]
     */
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        return Target::query()
                     ->where('user_id', '=', $user->id)
                     ->get();
    }

    /**
     * @param $request
     * @param Authenticatable $user
     * @return Target|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $target = new Target([
            "name" => $request['name'],
            "target_url" => $request['targetUrl'],
            "host_name" => $request['hostName'],
        ]);
        $target->user()->associate($user->id);
        $target->save();

        return $target;
    }

    /**
     * @param Target $model
     * @param $request
     * @param Authenticatable $user
     * @return Target|array
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->name = $request['name'];
        $model->target_url = $request['targetUrl'];
        $model->host_name = $request['hostName'];
        $model->save();

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

        BackupStep::query()
                  ->where('backup_id', '=', $model->id)
                  ->delete();

        return true;
    }
}
