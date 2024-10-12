<?php

namespace App\Services\Gaming;

use Exception;
use PhpMqtt\Client\MqttClient;

class MqttService
{
    /**
     * @throws Exception
     */
    private static function sendMessage(string $topic, string $message): void
    {
        try {
            $mqtt = new MqttClient('10.5.10.11', 1883, 'projects-api');
            $mqtt->connect();
            $mqtt->publish($topic, $message);
            $mqtt->disconnect();
        } catch (Exception $exception) {
            throw new Exception($exception);
        }
    }

    /**
     * @throws Exception
     */
    public static function deviceSetName(string $communicationId, string $name): void
    {
        self::sendMessage("gamingDevice/{$communicationId}/setName", $name);
    }
}
