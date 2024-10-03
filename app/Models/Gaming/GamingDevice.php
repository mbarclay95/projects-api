<?php

namespace App\Models\Gaming;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mbarclay36\LaravelCrud\ApiModel;
use PhpMqtt\Client\MqttClient;

/**
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string device_communication_id
 * @property string temp_name
 * @property Carbon last_seen
 */
class GamingDevice extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'device_communication_id', 'last_seen', 'temp_name'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    public function testingMqtt(): void
    {
        $mqtt = new MqttClient('10.5.10.11', 1883, 'testing');
        $mqtt->connect();
        $mqtt->publish("gamingDevice/private/{$this->device_communication_id}", 'Huey');
        $mqtt->disconnect();
    }

}
