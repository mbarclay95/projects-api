<?php

namespace App\Http\Controllers\Gaming;

use App\Models\Gaming\GamingSession;
use App\Models\Gaming\GamingSessionDevice;
use App\Services\Gaming\ActiveSessionService;
use App\Services\Gaming\GamingBroadcastService;
use Exception;
use Illuminate\Http\JsonResponse;
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
        'startedAt' => 'nullable|date',
        'endedAt' => 'nullable|date',
        'turnOrderType' => 'required|string',
        'currentTurn' => 'required|int',
        'allowTurnPassing' => 'required|bool',
        'skipAfterPassing' => 'required|bool',
        'pauseAtBeginningOfRound' => 'required|bool',
        'isPaused' => 'required|bool',
        'turnLimitSeconds' => 'required|int'
    ];
    protected static array $destroyRules = [];

    /**
     * @throws Exception
     */
    public function updateGamingDeviceTurnOrders(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sessionId' => 'required|int',
            'data' => 'required|array',
            'data.*.id' => 'required|int',
            'data.*.turnOrder' => 'required|int',
        ]);

        foreach ($validated['data'] as $updatedTurnOrder) {
            GamingSessionDevice::query()
                               ->where('gaming_session_id', '=', $validated['sessionId'])
                               ->where('id', '=', $updatedTurnOrder['id'])
                               ->update(['current_turn_order' => $updatedTurnOrder['turnOrder']]);
        }
        $session = GamingSession::query()
                                ->with('gamingSessionDevices.gamingDevice')
                                ->find($validated['sessionId']);

        ActiveSessionService::sendConfigToAllDevices($session);
        GamingBroadcastService::broadcastSessions();

        return new JsonResponse(['success' => true]);
    }
}
