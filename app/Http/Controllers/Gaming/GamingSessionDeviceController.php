<?php

namespace App\Http\Controllers\Gaming;

use App\Http\Controllers\Controller;
use App\Models\Gaming\GamingSessionDevice;
use Illuminate\Http\Request;
use Mbarclay36\LaravelCrud\CrudController;

class GamingSessionDeviceController extends CrudController
{
    protected static string $modelClass = GamingSessionDevice::class;
    protected static bool $storeAuth = false;
    protected static bool $updateAuth = false;
    protected static bool $destroyAuth = false;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
        'gamingDevice' => 'required|array',
        'gamingDevice.id' => 'required|int',
        'gamingSessionId' => 'required|int',
        'turnTimeDisplayMode' => 'required|string',
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'turnTimeDisplayMode' => 'required|string',
    ];
    protected static array $destroyRules = [];
}
