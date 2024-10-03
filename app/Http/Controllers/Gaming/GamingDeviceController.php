<?php

namespace App\Http\Controllers\Gaming;

use App\Http\Controllers\Controller;
use App\Models\Gaming\GamingDevice;
use Illuminate\Http\Request;
use Mbarclay36\LaravelCrud\CrudController;

class GamingDeviceController extends CrudController
{
    protected static string $modelClass = GamingDevice::class;
    protected static bool $indexAuth = false;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'deviceCommunicationId' => 'required|string',
    ];
    protected static array $updateRules = [
        'deviceCommunicationId' => 'required|string',
    ];
    protected static array $destroyRules = [];
}
