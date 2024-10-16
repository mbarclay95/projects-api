<?php

namespace App\Models\Gaming;

use App\Services\Gaming\ActiveSessionService;
use App\Services\Gaming\MqttService;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Mbarclay36\LaravelCrud\ApiModel;

/**
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string device_communication_id
 * @property Carbon last_seen
 * @property string button_color
 */
class GamingDevice extends ApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'device_communication_id', 'last_seen', 'button_color'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    /**
     * @throws Exception
     */
    public function sendNameChange(string $name): void
    {
        MqttService::deviceSetConfig($this, ['playerName' => $name]);
    }

    /**
     * @throws Exception
     */
    public function sendDeviceConfig(array $config): void
    {
        MqttService::deviceSetConfig($this, $config);
    }

    public function updateLastSeen(bool $save = true): void
    {
        $this->last_seen = Carbon::now('America/Los_Angeles');
        if ($save) {
            $this->save();
        }
    }

    /**
     * @throws Exception
     */
    public function initialize(): void
    {
        $this->checkForReinitialization();
        $this->updateLastSeen();
    }

    /**
     * @throws Exception
     */
    private function checkForReinitialization(): void
    {
        $sessionDevice = $this->getActiveSessionDevice();
        if ($sessionDevice) {
            $this->sendDeviceConfig(ActiveSessionService::getConfig($sessionDevice->gamingSession, $sessionDevice));
        }
    }

    /**
     * @throws Exception
     */
    public function handleButtonPress(): void
    {
        $session = $this->getActiveSession();
        if ($session) {
            $sessionService = new ActiveSessionService($session);
            $sessionService->handleButtonPress();
        }
    }

    private function getActiveSessionDevice(): GamingSessionDevice|null
    {
        return GamingSessionDevice::query()
                                  ->with('gamingSession')
                                  ->where('gaming_device_id', '=', $this->id)
                                  ->whereHas('gamingSession', function ($query) {
                                      $query->whereNotNull('started_at')
                                            ->whereNull('ended_at');
                                  })
                                  ->first();
    }

    private function getActiveSession(): GamingSession|null
    {
        return GamingSession::query()
                            ->with('gamingSessionDevices.gamingDevice')
                            ->whereNotNull('started_at')
                            ->whereNull('ended_at')
                            ->whereExists(function ($query) {
                                $query->select(DB::raw(1))
                                      ->from('gaming_session_devices')
                                      ->where('gaming_session_devices.gaming_session_id', '=', 'gaming_sessions.id')
                                      ->where('gaming_session_devices.gaming_device_id', '=', $this->id);
                            })
                            ->first();
    }

}
