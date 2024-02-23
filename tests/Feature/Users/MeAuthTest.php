<?php

namespace Tests\Feature\Users;

use App\Enums\Roles;
use App\Models\Users\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MeAuthTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->createFirstUserConfig();
    }

    public function testSetDashboardRole()
    {
        /** @var Role $dashboardRole */
        $dashboardRole = Role::query()->where('name', '=', Roles::DASHBOARD_ROLE)->first();

        $this->sendUpdateRequest([
            'name' => $this->user->name,
            'userConfig' => [
                'sideMenuOpen' => 0,
                'homePageRole' => null,
                'moneyAppToken' => null,
            ],
            'roles' => [
                [
                    'id' => $dashboardRole->id,
                    'name' => $dashboardRole->name
                ]
            ]
        ]);

        $userRoles = $this->user->roles()->get();
        self::assertEquals(1, $userRoles->count());
        self::assertEquals($dashboardRole->id, $userRoles->first()->id);
    }

    public function testSettingAdminRoleWithoutAdmin(): void
    {
        /** @var Role $dashboardRole */
        $dashboardRole = Role::query()->where('name', '=', Roles::DASHBOARD_ROLE)->first();
        /** @var Role $adminRole */
        $adminRole = Role::query()->where('name', '=', Roles::ADMIN_ROLE)->first();

        $this->sendUpdateRequest([
            'name' => $this->user->name,
            'userConfig' => [
                'sideMenuOpen' => 0,
                'homePageRole' => null,
                'moneyAppToken' => null,
            ],
            'roles' => [
                [
                    'id' => $dashboardRole->id,
                    'name' => $dashboardRole->name
                ],
                [
                    'id' => $adminRole->id,
                    'name' => $adminRole->name
                ],
            ]
        ]);

        $userRoles = $this->user->roles()->get();
        self::assertEquals(1, $userRoles->count());
        self::assertEquals($dashboardRole->id, $userRoles->first()->id);
    }

    public function testSettingAdminRoleWithAdmin(): void
    {
        /** @var Role $dashboardRole */
        $dashboardRole = Role::query()->where('name', '=', Roles::DASHBOARD_ROLE)->first();
        /** @var Role $adminRole */
        $adminRole = Role::query()->where('name', '=', Roles::ADMIN_ROLE)->first();
        $this->user->syncRoles([$adminRole]);

        $this->sendUpdateRequest([
            'name' => $this->user->name,
            'userConfig' => [
                'sideMenuOpen' => 0,
                'homePageRole' => null,
                'moneyAppToken' => null,
            ],
            'roles' => [
                [
                    'id' => $dashboardRole->id,
                    'name' => $dashboardRole->name
                ],
                [
                    'id' => $adminRole->id,
                    'name' => $adminRole->name
                ],
            ]
        ]);

        $userRoles = $this->user->roles()->get();
        self::assertEquals(2, $userRoles->count());
        self::assertTrue($userRoles->contains(function ($role) use ($dashboardRole) {
            return $role->id == $dashboardRole->id;
        }));
        self::assertTrue($userRoles->contains(function ($role) use ($adminRole) {
            return $role->id == $adminRole->id;
        }));
    }

    private function sendUpdateRequest(array $requestBody): void
    {
        $token = auth()->login($this->user);
        $response = $this->actingAs($this->user)
                         ->patch('api/update-me', $requestBody, [
                             'accept' => 'application/json',
                             'authorization' => 'Bearer ' . $token
                         ]);
        $response->assertSuccessful();
    }
}
