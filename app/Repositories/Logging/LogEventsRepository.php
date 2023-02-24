<?php

namespace App\Repositories\Logging;

use App\Models\Logging\LogEvent;
use App\Models\Logging\LogItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class LogEventsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param User $user
     * @return Model|array
     */
    public function createEntity($request, User $user): Model|array
    {
        $logEvent = new LogEvent([
            'source' => $request['source']
        ]);
        $logEvent->save();

        foreach ($request['results'] as $result) {
            clock($result);
            $item = [
                'result' => $result,
                'logEventId' => $logEvent->id
            ];
            LogItem::createEntity($item, $user);
        }

        return $logEvent;
    }
}
