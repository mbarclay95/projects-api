<?php

namespace App\Repositories\Gaming;

use App\Models\Gaming\GamingDevice;
use App\Models\Gaming\GamingSession;
use App\Models\Gaming\GamingSessionDevice;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GamingSessionDevicesRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return GamingSessionDevice|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $model = new GamingSessionDevice([
            'name' => $request['name'],
            'metadata' => [],
        ]);
        $model->gamingDevice()->associate($request['gamingDevice']['id']);
        $model->gamingSession()->associate($request['gamingSessionId']);
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
