<?php

namespace App\Repositories\Logging;

use App\Models\Logging\LogItem;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\DefaultRepository;

class LogItemsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @return Model|array
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $logItem = new LogItem([
            'payload' => $request['result']
        ]);
        $logItem->logEvent()->associate($request['logEventId']);
        $logItem->save();

        return $logItem;
    }
}
