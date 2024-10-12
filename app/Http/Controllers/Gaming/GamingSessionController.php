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
        'name' => 'required|string',
        'turnOrderType' => 'required|string',
        'allowTurnPassing' => 'required|bool',
        'skipAfterPassing' => 'required|bool',
        'pauseAtBeginningOfRound' => 'required|bool',
        'turnLimitSeconds' => 'required|int'
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'started_at' => 'nullable|date',
        'ended_at' => 'nullable|date',
        'turn_order_type' => 'required|string',
        'current_turn' => 'required|int',
        'allow_turn_passing' => 'required|bool',
        'skip_after_passing' => 'required|bool',
        'pause_at_beginning_of_round' => 'required|bool',
        'is_paused' => 'required|bool',
        'turn_limit_seconds' => 'required|int'
    ];
    protected static array $destroyRules = [];
}
