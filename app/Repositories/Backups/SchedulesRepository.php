<?php

namespace App\Repositories\Backups;

use App\Models\Backups\Schedule;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class SchedulesRepository extends DefaultRepository
{
    /**
     * @return Schedule|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        return parent::createEntity($request, $user);
    }
}
