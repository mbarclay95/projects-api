<?php

namespace App\Repositories\Gaming;

use App\Models\Gaming\GamingSession;
use App\Models\Gaming\GamingSessionDevice;
use App\Services\Gaming\ActiveSessionService;
use App\Services\Gaming\GamingBroadcastService;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GamingSessionDevicesRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return GamingSessionDevice|array
     * @throws Exception
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

        ActiveSessionService::sendConfigToDevice($model);
        GamingBroadcastService::broadcastSessions();

        return $model;
    }

    /**
     * @param GamingSessionDevice $model
     * @param $request
     * @param Authenticatable $user
     * @return GamingSessionDevice|array
     * @throws Exception
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->name = $request['name'];
        $model->turn_time_display_mode = $request['turnTimeDisplayMode'];
        $model->save();

        ActiveSessionService::sendConfigToDevice($model);
        GamingBroadcastService::broadcastSessions();

        return $model;
    }

    /**
     * @param GamingSessionDevice $model
     * @param Authenticatable $user
     * @return bool
     * @throws Exception
     */
    public function destroyEntity(Model $model, Authenticatable $user): bool
    {
        $modelTurnOrder = $model->current_turn_order;
        $sessionId = $model->gaming_session_id;
        ActiveSessionService::clearDeviceConfig($model->gamingDevice);
        $model->delete();
        $sessionDevices = GamingSessionDevice::query()
                                             ->where('gaming_session_id', '=', $sessionId)
                                             ->where('current_turn_order', '>', $modelTurnOrder)
                                             ->get();

        /** @var GamingSessionDevice $sessionDevice */
        foreach ($sessionDevices as $sessionDevice) {
            $sessionDevice->current_turn_order -= 1;
            $sessionDevice->save();
        }
        $session = GamingSession::query()->find($sessionId);
        if ($session->current_turn >= $modelTurnOrder) {
            $session->current_turn -= 1;
            $session->save();
        }

        ActiveSessionService::sendConfigToAllDevices($session);
        GamingBroadcastService::broadcastSessions();

        return true;
    }
}
