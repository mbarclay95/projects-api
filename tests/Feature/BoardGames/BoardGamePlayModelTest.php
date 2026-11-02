<?php

namespace Tests\Feature\BoardGames;

use App\Enums\BoardGamePlayStatusEnum;
use App\Enums\BoardGameTypeEnum;
use App\Models\BoardGames\BoardGamePlay;
use Tests\TestCase;

class BoardGamePlayModelTest extends TestCase
{
    public function test_a_factory_play_defaults_to_an_in_progress_horror_game(): void
    {
        $play = BoardGamePlay::factory()->create();

        $fresh = BoardGamePlay::query()->findOrFail($play->id);

        $this->assertSame(BoardGamePlayStatusEnum::IN_PROGRESS, $fresh->status);
        $this->assertSame(BoardGameTypeEnum::HORROR_GAME, $fresh->game_type);
    }

    public function test_state_round_trips_as_an_array(): void
    {
        $play = BoardGamePlay::factory()->create(['state' => ['turn' => 1]]);

        $this->assertSame(['turn' => 1], BoardGamePlay::query()->findOrFail($play->id)->state);
    }
}
