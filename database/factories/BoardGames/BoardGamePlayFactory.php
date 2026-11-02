<?php

namespace Database\Factories\BoardGames;

use App\Enums\BoardGamePlayStatusEnum;
use App\Enums\BoardGameTypeEnum;
use App\Models\BoardGames\BoardGamePlay;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoardGamePlay>
 */
class BoardGamePlayFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'game_type' => BoardGameTypeEnum::HORROR_GAME,
            'name' => null,
            'status' => BoardGamePlayStatusEnum::IN_PROGRESS,
            'state' => null,
        ];
    }
}
