<?php

namespace App\Services\Gaming;

use App\Models\Gaming\GamingDevice;
use App\Models\Gaming\GamingSession;
use App\Models\Users\User;
use App\Repositories\Gaming\GamingDevicesRepository;
use App\Repositories\Gaming\GamingSessionsRepository;
use Exception;
use Illuminate\Support\Collection;

class GamingBroadcastService
{
    private static function createDummyUser(): User
    {
        return new User([]);
    }

    /**
     * @param Collection|GamingSession[]|null $sessions
     * @return void
     * @throws Exception
     */
    public static function broadcastSessions(Collection|array|null $sessions = null): void
    {
        if ($sessions == null) {
            $sessions = GamingSessionsRepository::getEntitiesStatic([], self::createDummyUser(), false);
        }
        MqttService::broadcastToWs([
            'event' => 'gamingSessions',
            'data' => GamingSession::toApiModels($sessions)
        ]);
    }

    /**
     * @param Collection|GamingDevice[]|null $devices
     * @return void
     * @throws Exception
     */
    public static function broadcastDevices(Collection|array|null $devices = null): void
    {
        if ($devices == null) {
            $devices = GamingDevicesRepository::getEntitiesStatic([], self::createDummyUser(), false);
        }
        MqttService::broadcastToWs([
            'event' => 'gamingDevices',
            'data' => GamingDevice::toApiModels($devices)
        ]);
    }
}
