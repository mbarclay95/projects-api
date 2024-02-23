<?php

namespace App\Http\Controllers\Logging;

use App\Enums\LogSourceEnum;
use App\Http\Controllers\Controller;
use App\Models\Logging\LogEvent;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoggingController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function logSmartResults(Request $request): JsonResponse
    {
        $rules = [
            'results' => 'array|required',
            'results.*.drive' => 'required|string',
            'results.*.result' => 'required|int'
        ];
        $validated = $request->validate($rules);
        $validated['source'] = LogSourceEnum::ProxmoxSmartLogs;
        $dummyUser = new User();

        $logEvent = LogEvent::createEntity($validated, $dummyUser);

        return new JsonResponse($logEvent);
    }

    /**
     * @return JsonResponse
     */
    public function validateSmartLogs(): JsonResponse
    {
        $now = Carbon::now();
        /** @var LogEvent $lastLogEvent */
        $lastLogEvent = LogEvent::query()
                                ->where('source', '=', LogSourceEnum::ProxmoxSmartLogs)
                                ->orderBy('created_at', 'desc')
                                ->first();

        $hourDiff = $now->diffInHours($lastLogEvent->created_at);
        $itemCount = $lastLogEvent->logItems()->count();

        return new JsonResponse([
            'hourDiff' => $hourDiff,
            'itemCount' => $itemCount
        ]);
    }
}

