<?php

namespace App\Repositories\Logging;

use App\Models\Logging\LogEvent;
use App\Models\Logging\LogItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class LogItemsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param User $user
     * @return Model|array
     */
    public function createEntity($request, User $user): Model|array
    {
        $logItem = new LogItem([
            'payload' => $request['result']
        ]);
        $logItem->logEvent()->associate($request['logEventId']);
        $logItem->save();

        return $logItem;
    }
}
