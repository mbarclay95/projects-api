<?php

namespace App\Repositories\Gaming;

use App\Models\Gaming\GamingDevice;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GamingDevicesRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return GamingDevice|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $device = new GamingDevice([
            'device_communication_id' => $request['deviceCommunicationId']
        ]);
        $device->save();

        return $device;
    }

    /**
     * @param GamingDevice $model
     * @param $request
     * @param Authenticatable $user
     * @return GamingDevice|array
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->device_communication_id = $request['deviceCommunicationId'];
        $model->save();

        return $model;
    }
}
