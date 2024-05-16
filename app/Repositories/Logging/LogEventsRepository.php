<?php

namespace App\Repositories\Logging;

use App\Models\Logging\LogEvent;
use App\Models\Logging\LogItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class LogEventsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @param bool $viewOnlyForUser
     * @return Collection|array
     */
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        /** @var LogEvent[] $logEvents */
        $logEvents = LogEvent::query()
                             ->with('logItems')
                             ->orderBy('created_at', 'desc')
                             ->limit(20)
                             ->get();

        return $logEvents;
    }

    /**
     * @param $request
     * @param Authenticatable $user
     * @return Model|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $logEvent = new LogEvent([
            'source' => $request['source']
        ]);
        $logEvent->save();

        foreach ($request['results'] as $result) {
            $item = [
                'result' => $result,
                'logEventId' => $logEvent->id
            ];
            LogItem::createEntity($item, $user);
        }

        return LogEvent::toApiModel($logEvent);
    }
}
