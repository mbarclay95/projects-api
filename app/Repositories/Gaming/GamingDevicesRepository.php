<?php

namespace App\Repositories\Gaming;

use App\Models\Gaming\GamingDevice;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GamingDevicesRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @param bool $viewOnlyForUser
     * @return Collection|array
     */
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        return GamingDevice::query()
                           ->orderBy('button_color')
                           ->get();
    }

    /**
     * @param $request
     * @param Authenticatable $user
     * @return GamingDevice|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $device = new GamingDevice([
            'device_communication_id' => $request['deviceCommunicationId'],
            'last_seen' => Carbon::now('America/Los_Angeles')->subDay(),
            'button_color' => $request['buttonColor'],
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
        $model->button_color = $request['buttonColor'];
        $model->save();

        return $model;
    }
}
