<?php

namespace App\Http\Controllers\Gaming;

use App\Http\Controllers\Controller;
use App\Models\Gaming\GamingSession;
use Illuminate\Http\Request;
use Mbarclay36\LaravelCrud\CrudController;

class GamingSessionController extends CrudController
{
    protected static string $modelClass = GamingSession::class;
    protected static bool $indexAuth = false;
    protected static bool $storeAuth = false;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'deviceCommunicationId' => 'required|string',
    ];
    protected static array $updateRules = [
        'deviceCommunicationId' => 'required|string',
    ];
    protected static array $destroyRules = [];
}
