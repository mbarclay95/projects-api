<?php

namespace App\Console\Commands\Gaming;

use App\Models\Gaming\GamingDevice;
use Illuminate\Console\Command;

class DeviceCommunication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gaming:device-comm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        /** @var GamingDevice $gamingDevice */
        $gamingDevice = GamingDevice::query()->find(1);
//        $gamingDevice->testingMqtt();
    }
}
