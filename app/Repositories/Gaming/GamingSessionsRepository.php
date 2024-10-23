<?php

namespace App\Repositories\Gaming;

use App\Models\Gaming\GamingSession;
use App\Services\Gaming\ActiveSessionService;
use App\Services\Gaming\GamingBroadcastService;
use Exception;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class GamingSessionsRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @param bool $viewOnlyForUser
     * @return Collection|array
     */
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        $showWithArchived = array_key_exists('withArchived', $request) && $request['withArchived'];
        return GamingSession::query()
                            ->with('gamingSessionDevices.gamingDevice')
                            ->when(!$showWithArchived, function ($query) {
                                return $query->whereNull('ended_at');
                            })
                            ->orderBy('created_at', 'desc')
                            ->get();
    }

    /**
     * @param $request
     * @param Authenticatable $user
     * @return GamingSession|array
     * @throws Exception
     */
    public function createEntity($request, Authenticatable $user): Model|array
    {
        $model = new GamingSession([
            'name' => $request['name'],
            'started_at' => null,
            'ended_at' => null,
            'turn_order_type' => $request['turnOrderType'],
            'current_turn' => 1,
            'allow_turn_passing' => $request['allowTurnPassing'],
            'skip_after_passing' => $request['skipAfterPassing'],
            'pause_at_beginning_of_round' => $request['pauseAtBeginningOfRound'],
            'is_paused' => false,
            'turn_limit_seconds' => $request['turnLimitSeconds'],
        ]);
        $model->save();

        GamingBroadcastService::broadcastSessions();

        return $model;
    }

    /**
     * @param GamingSession $model
     * @param $request
     * @param Authenticatable $user
     * @return GamingSession|array
     * @throws Exception
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $deviceChangeMade = false;
        if (
            $model->started_at == null && $request['startedAt'] != null || //session started
            $model->is_paused != $request['isPaused'] || //pause change
            $model->current_turn != $request['currentTurn'] || //turn order change
            $model->turn_limit_seconds != $request['turnLimitSeconds'] || //turn length change
            $model->allow_turn_passing != $request['allowTurnPassing'] // turn passing change
        ) {
            $deviceChangeMade = true;
        }
        $model->name = $request['name'];
        $model->started_at = $request['startedAt'];
        $model->ended_at = $request['endedAt'];
        $model->turn_order_type = $request['turnOrderType'];
        $model->current_turn = $request['currentTurn'];
        $model->allow_turn_passing = $request['allowTurnPassing'];
        $model->skip_after_passing = $request['skipAfterPassing'];
        $model->pause_at_beginning_of_round = $request['pauseAtBeginningOfRound'];
        $model->is_paused = $request['isPaused'];
        $model->turn_limit_seconds = $request['turnLimitSeconds'];
        $model->save();

        if ($deviceChangeMade) {
            $model->load('gamingSessionDevices.gamingDevice');
            ActiveSessionService::sendConfigToAllDevices($model);
        }
        GamingBroadcastService::broadcastSessions();

        return $model;
    }
}
