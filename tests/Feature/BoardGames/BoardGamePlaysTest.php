<?php

namespace Tests\Feature\BoardGames;

use App\Enums\BoardGamePlayStatusEnum;
use App\Enums\BoardGameTypeEnum;
use App\Enums\Roles;
use App\Models\BoardGames\BoardGamePlay;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BoardGamePlaysTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::BOARD_GAMES_ROLE);
    }

    public function test_the_index_returns_only_the_callers_plays_without_state(): void
    {
        $mine = BoardGamePlay::factory()->create(['user_id' => $this->user->id, 'state' => ['turn' => 3]]);
        BoardGamePlay::factory()->create();

        $response = $this->jsonAs($this->user, 'GET', 'api/board-game-plays')
            ->assertSuccessful()
            ->json();

        self::assertCount(1, $response);
        self::assertEquals($mine->id, $response[0]['id']);
        self::assertEquals('horror-game', $response[0]['gameType']);
        self::assertArrayHasKey('name', $response[0]);
        self::assertEquals('in_progress', $response[0]['status']);
        self::assertArrayNotHasKey('state', $response[0]);
    }

    public function test_show_returns_the_callers_play_with_state(): void
    {
        $play = BoardGamePlay::factory()->create(['user_id' => $this->user->id, 'state' => ['turn' => 3]]);

        $this->jsonAs($this->user, 'GET', "api/board-game-plays/{$play->id}")
            ->assertSuccessful()
            ->assertJsonPath('id', $play->id)
            ->assertJsonPath('state.turn', 3);
    }

    public function test_show_on_another_users_play_is_not_found(): void
    {
        $play = BoardGamePlay::factory()->create();

        $this->jsonAs($this->user, 'GET', "api/board-game-plays/{$play->id}")
            ->assertNotFound();
    }

    public function test_store_with_only_a_game_type_uses_defaults(): void
    {
        $this->jsonAs($this->user, 'POST', 'api/board-game-plays', ['gameType' => 'horror-game'])
            ->assertSuccessful()
            ->assertJsonPath('status', 'in_progress')
            ->assertJsonPath('name', null)
            ->assertJsonPath('state', null);
    }

    public function test_store_with_a_name_stores_it(): void
    {
        $this->jsonAs($this->user, 'POST', 'api/board-game-plays', ['gameType' => 'horror-game', 'name' => 'Friday'])
            ->assertSuccessful()
            ->assertJsonPath('name', 'Friday');

        self::assertSame('Friday', BoardGamePlay::query()->where('user_id', $this->user->id)->first()->name);
    }

    public function test_store_with_an_unknown_game_type_is_rejected(): void
    {
        $this->jsonAs($this->user, 'POST', 'api/board-game-plays', ['gameType' => 'nope'])
            ->assertStatus(422);
    }

    public function test_update_with_only_state_leaves_name_and_status(): void
    {
        $play = BoardGamePlay::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Keep',
            'status' => BoardGamePlayStatusEnum::LOST,
        ]);

        $this->jsonAs($this->user, 'PUT', "api/board-game-plays/{$play->id}", ['state' => ['turn' => 2]])
            ->assertSuccessful();

        $play = $play->fresh();
        self::assertSame(['turn' => 2], $play->state);
        self::assertSame('Keep', $play->name);
        self::assertSame(BoardGamePlayStatusEnum::LOST, $play->status);
    }

    public function test_update_can_clear_the_name(): void
    {
        $play = BoardGamePlay::factory()->create(['user_id' => $this->user->id, 'name' => 'Gone']);

        $this->jsonAs($this->user, 'PUT', "api/board-game-plays/{$play->id}", ['name' => null])
            ->assertSuccessful();

        self::assertNull($play->fresh()->name);
    }

    public function test_update_sets_status(): void
    {
        $play = BoardGamePlay::factory()->create(['user_id' => $this->user->id]);

        $this->jsonAs($this->user, 'PUT', "api/board-game-plays/{$play->id}", ['status' => 'won'])
            ->assertSuccessful()
            ->assertJsonPath('status', 'won');

        self::assertSame(BoardGamePlayStatusEnum::WON, $play->fresh()->status);
    }

    public function test_update_with_an_invalid_status_is_rejected(): void
    {
        $play = BoardGamePlay::factory()->create(['user_id' => $this->user->id]);

        $this->jsonAs($this->user, 'PUT', "api/board-game-plays/{$play->id}", ['status' => 'drawn'])
            ->assertStatus(422);
    }

    public function test_update_cannot_change_the_game_type(): void
    {
        $play = BoardGamePlay::factory()->create(['user_id' => $this->user->id]);

        $this->jsonAs($this->user, 'PUT', "api/board-game-plays/{$play->id}", ['gameType' => 'other', 'name' => 'x'])
            ->assertSuccessful();

        self::assertSame(BoardGameTypeEnum::HORROR_GAME, $play->fresh()->game_type);
    }

    public function test_update_and_delete_on_another_users_play_are_unauthenticated(): void
    {
        $play = BoardGamePlay::factory()->create();

        $this->jsonAs($this->user, 'PUT', "api/board-game-plays/{$play->id}", ['name' => 'x'])
            ->assertStatus(401);
        $this->jsonAs($this->user, 'DELETE', "api/board-game-plays/{$play->id}")
            ->assertStatus(401);

        self::assertNotNull($play->fresh());
    }

    public function test_update_and_delete_on_a_nonexistent_id_are_not_found(): void
    {
        $this->jsonAs($this->user, 'PUT', 'api/board-game-plays/999999', ['name' => 'x'])
            ->assertNotFound();
        $this->jsonAs($this->user, 'DELETE', 'api/board-game-plays/999999')
            ->assertNotFound();
    }

    public function test_delete_removes_the_row(): void
    {
        $play = BoardGamePlay::factory()->create(['user_id' => $this->user->id]);

        $this->jsonAs($this->user, 'DELETE', "api/board-game-plays/{$play->id}")
            ->assertSuccessful();

        $this->assertDatabaseMissing('board_game_plays', ['id' => $play->id]);
    }

    public function test_a_user_without_the_role_is_unauthenticated_on_index(): void
    {
        $this->jsonAs(User::factory()->create(), 'GET', 'api/board-game-plays')
            ->assertStatus(401);
    }

    private function jsonAs(User $user, string $method, string $uri, array $data = []): TestResponse
    {
        $token = auth()->login($user);

        return $this->actingAs($user)->json($method, $uri, $data, [
            'accept' => 'application/json',
            'authorization' => 'Bearer '.$token,
        ]);
    }
}
