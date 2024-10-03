<?php

namespace App\Repositories\Gaming;

use App\Models\Gaming\GamingDevice;
use App\Models\Gaming\GamingSession;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GamingSessionsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return GamingSession|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $model = new GamingSession([
            'name' => $request['name'],
//            'code' => ,
            'session_type' => $request['sessionType'],
            'is_active' => true,
        ]);
        $model->save();

        return $model;
    }

    /**
     * @param GamingSession $model
     * @param $request
     * @param Authenticatable $user
     * @return GamingSession|array
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->name = $request['name'];
        $model->session_type = $request['sessionType'];
        $model->is_active = $request['isActive'];
        $model->save();

        return $model;
    }
}
