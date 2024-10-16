<?php

namespace App\Services\Gaming;

use App\Models\Gaming\GamingSession;
use App\Models\Gaming\GamingSessionDevice;
use Exception;

class ActiveSessionService
{
    private GamingSession $gamingSession;

    public function __construct(GamingSession $gamingSession)
    {
        $this->gamingSession = $gamingSession;
    }

    /**
     * @throws Exception
     */
    public function beginSession(): void
    {
        foreach ($this->gamingSession->gamingSessionDevices as $sessionDevice) {
            $config = self::getConfig($this->gamingSession, $sessionDevice);
            $sessionDevice->gamingDevice->sendDeviceConfig($config);
        }
    }

    public static function getConfig(GamingSession $session, GamingSessionDevice $sessionDevice): array
    {
        return [
            'turnLength' => $session->turn_limit_seconds,
            'playerName' => $sessionDevice->name,
            'isTurn' => $sessionDevice->current_turn_order == 1,
            'currentTurnOrder' => $sessionDevice->current_turn_order,
            'waiting' => $sessionDevice->current_turn_order == 1 && $session->pause_at_beginning_of_round,
            'turnDisplayMode' => $sessionDevice->turn_time_display_mode,
            'passed' => false,
        ];
    }
}
