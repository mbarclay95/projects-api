<?php

namespace App\Services\Gaming;

use App\Models\Gaming\GamingSession;
use App\Models\Gaming\GamingSessionDevice;
use Exception;

class ActiveSessionService
{
    /**
     * @throws Exception
     */
    public static function sendConfigToAllDevices(GamingSession $session): void
    {
        foreach ($session->gamingSessionDevices as $sessionDevice) {
            $config = self::getConfig($session, $sessionDevice);
            $sessionDevice->gamingDevice->sendDeviceConfig($config);
        }
    }

    /**
     * @throws Exception
     */
    public static function handleButtonPress(GamingSession $session): void
    {
        if ($session->is_paused) {
            $session->is_paused = false;
            $session->save();
            return;
        }

        $session->current_turn += 1;
        $session->current_turn = (($session->current_turn - 1) % count($session->gamingSessionDevices)) + 1;
        $session->save();
        self::sendConfigToAllDevices($session);
    }

    public static function getConfig(GamingSession $session, GamingSessionDevice $sessionDevice): array
    {
        return [
            'turnLength' => $session->turn_limit_seconds,
            'playerName' => $sessionDevice->name,
            'isTurn' => $sessionDevice->current_turn_order == $session->current_turn,
            'currentTurnOrder' => $sessionDevice->current_turn_order,
            'paused' => $sessionDevice->current_turn_order == $session->current_turn && $session->is_paused,
            'showNumericTime' => $sessionDevice->turn_time_display_mode === 'numeric',
            'showTimeGraph' => $sessionDevice->turn_time_display_mode === 'graph',
            'passed' => $sessionDevice->has_passed,
        ];
    }
}
