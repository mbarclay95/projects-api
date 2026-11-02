<?php

namespace Tests\Feature\BoardGames;

use App\Enums\Permissions;
use App\Enums\Roles;
use App\Models\BoardGames\BoardGamePlay;
use App\Models\Users\User;
use Tests\TestCase;

class BoardGamesRoleTest extends TestCase
{
    public function test_the_role_grants_the_scoped_play_permissions_and_the_page(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::BOARD_GAMES_ROLE);

        foreach ([
            BoardGamePlay::viewAnyForUserPermission(),
            BoardGamePlay::viewForUserPermission(),
            BoardGamePlay::createPermission(),
            BoardGamePlay::updateForUserPermission(),
            BoardGamePlay::deleteForUserPermission(),
            Permissions::VIEW_BOARD_GAMES_PAGE,
        ] as $permission) {
            $this->assertTrue($user->checkPermissionTo($permission), $permission);
        }
    }

    public function test_the_all_users_view_permission_is_not_declared(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Roles::BOARD_GAMES_ROLE);

        $this->assertFalse($user->checkPermissionTo(BoardGamePlay::viewAnyPermission()));
    }
}
