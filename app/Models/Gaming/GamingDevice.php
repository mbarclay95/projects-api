<?php

namespace App\Models\Gaming;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Mbarclay36\LaravelCrud\ApiModel;
use PhpMqtt\Client\Exceptions\ConfigurationInvalidException;
use PhpMqtt\Client\Exceptions\ConnectingToBrokerFailedException;
use PhpMqtt\Client\Exceptions\DataTransferException;
use PhpMqtt\Client\Exceptions\ProtocolNotSupportedException;
use PhpMqtt\Client\Exceptions\RepositoryException;
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

    /**
     * @throws ConfigurationInvalidException
     * @throws ConnectingToBrokerFailedException
     * @throws RepositoryException
     * @throws ProtocolNotSupportedException
     * @throws DataTransferException
     */
    public function testingMqtt(): void
    {
        $mqtt = new MqttClient('10.5.10.11', 1883, 'testing');
        $mqtt->connect();
        $mqtt->publish("gamingDevice/private/{$this->device_communication_id}", 'Huey');
        $mqtt->disconnect();
    }

    public function updateLastSeen(bool $save = true): void
    {
        $this->last_seen = Carbon::now();
        if ($save) {
            $this->save();
        }
    }

}
