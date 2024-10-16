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
    public function sendConfigToAllDevices(): void
    {
        foreach ($this->gamingSession->gamingSessionDevices as $sessionDevice) {
            $config = self::getConfig($this->gamingSession, $sessionDevice);
            $sessionDevice->gamingDevice->sendDeviceConfig($config);
        }
    }

    /**
     * @throws Exception
     */
    public function handleButtonPress(): void
    {
        if ($this->gamingSession->is_paused) {
            $this->gamingSession->is_paused = false;
            $this->gamingSession->save();
            return;
        }

        $this->gamingSession->current_turn += 1;
        $this->gamingSession->current_turn = (($this->gamingSession->current_turn - 1) % count($this->gamingSession->gamingSessionDevices)) + 1;
        $this->gamingSession->save();
        $this->sendConfigToAllDevices();
    }

    public static function getConfig(GamingSession $session, GamingSessionDevice $sessionDevice): array
    {
        return [
            'turnLength' => $session->turn_limit_seconds,
            'playerName' => $sessionDevice->name,
            'isTurn' => $sessionDevice->current_turn_order == $session->current_turn,
            'currentTurnOrder' => $sessionDevice->current_turn_order,
            'waiting' => $sessionDevice->current_turn_order == $session->current_turn && $session->is_paused,
            'turnDisplayMode' => $sessionDevice->turn_time_display_mode,
            'passed' => $sessionDevice->has_passed,
        ];
    }
}
