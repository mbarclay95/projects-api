<?php

namespace App\Repositories\BoardGames;

use App\Enums\BoardGamePlayStatusEnum;
use App\Models\BoardGames\BoardGamePlay;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Mbarclay36\LaravelCrud\DefaultRepository;

class BoardGamePlaysRepository extends DefaultRepository
{
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        return BoardGamePlay::query()
            ->where('user_id', '=', $user->id)
            ->orderBy('updated_at', 'desc')
            ->get(['id', 'user_id', 'game_type', 'name', 'status', 'created_at', 'updated_at']);
    }

    public function getEntity(int $id, Authenticatable $user, bool $viewOnlyForUser): Model|array
    {
        return BoardGamePlay::query()
            ->where('user_id', '=', $user->id)
            ->where('id', '=', $id)
            ->firstOrFail();
    }

    public function createEntity($request, Authenticatable $user): Model|array
    {
        $play = new BoardGamePlay([
            'user_id' => $user->id,
            'game_type' => $request['gameType'],
            'name' => $request['name'] ?? null,
            'status' => BoardGamePlayStatusEnum::IN_PROGRESS,
            'state' => null,
        ]);
        $play->save();

        return $play;
    }

    /**
     * @param  BoardGamePlay  $model
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        foreach (['name', 'status', 'state'] as $key) {
            if (array_key_exists($key, $request)) {
                $model->{$key} = $request[$key];
            }
        }
        $model->save();

        return $model;
    }
}
