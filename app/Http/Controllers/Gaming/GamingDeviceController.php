<?php

namespace App\Http\Controllers\Gaming;

use App\Http\Controllers\Controller;
use App\Models\Gaming\GamingDevice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Mbarclay36\LaravelCrud\CrudController;

class GamingDeviceController extends CrudController
{
    protected static string $modelClass = GamingDevice::class;
    protected static bool $indexAuth = false;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'deviceCommunicationId' => 'required|string',
        'buttonColor' => 'required|string',
    ];
    protected static array $updateRules = [
        'deviceCommunicationId' => 'required|string',
        'buttonColor' => 'required|string',
    ];
    protected static array $destroyRules = [];

    public function deviceAction(Request $request, string $deviceCommunicationId): JsonResponse
    {
        /** @var GamingDevice $device */
        $device = GamingDevice::query()
                              ->where('device_communication_id', '=', $deviceCommunicationId)
                              ->first();
        if (!$device) {
            abort(400);
        }

        $validated = $request->validate([
            'action' => 'required|string'
        ]);

        match ($validated['action']) {
            'ping' => $device->updateLastSeen(),
            default => abort(400, 'Invalid action')
        };

        return new JsonResponse(['success' => true]);
    }
}
