<?php

namespace App\Repositories\Gaming;

use App\Models\Gaming\GamingSessionDevice;
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
        $sessionDeviceCount = GamingSessionDevice::query()
                                                 ->where('gaming_session_id', '=', $request['gamingSessionId'])
                                                 ->count();
        $model = new GamingSessionDevice([
            'name' => $request['name'],
            'current_turn_order' => $sessionDeviceCount + 1,
            'next_turn_order' => null,
            'turn_time_display_mode' => $request['turnTimeDisplayMode'],
            'skip' => false,
            'has_passed' => false
        ]);
        $model->gamingDevice()->associate($request['gamingDevice']['id']);
        $model->gamingSession()->associate($request['gamingSessionId']);
        $model->save();

        $model->gamingDevice->sendNameChange($request['name']);

        return $model;
    }

    /**
     * @param GamingSessionDevice $model
     * @param $request
     * @param Authenticatable $user
     * @return GamingSessionDevice|array
     * @throws \Exception
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        if ($model->name != $request['name']) {
            $model->gamingDevice->sendNameChange($request['name']);
        }
        $model->name = $request['name'];
        $model->turn_time_display_mode = $request['turnTimeDisplayMode'];
        $model->save();

        return $model;
    }
}
