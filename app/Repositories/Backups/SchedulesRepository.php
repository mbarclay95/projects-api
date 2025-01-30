<?php

namespace App\Repositories\Backups;

use App\Models\Backups\Backup;
use App\Models\Backups\BackupStep;
use App\Models\Backups\Schedule;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class SchedulesRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return Schedule|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        return parent::createEntity($request, $user);
    }
}
