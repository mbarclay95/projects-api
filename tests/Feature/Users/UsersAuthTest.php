<?php

namespace Tests\Feature\Users;

use App\Enums\Roles;
use App\Models\Users\User;
use Tests\Feature\Auth\AuthTestCase;

class UsersAuthTest extends AuthTestCase
{
    /**
     * INDEX
     * @return void
     */
    public function testGetUsers()
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        $this->runTestsGET('api/users');
    }

    /**
     * STORE
     * @return void
     */
    public function testPostUsers(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        $this->runTestsPOST("api/users");
    }

    /**
     * UPDATE
     * @return void
     */
    public function testPutUsers(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        /** @var User $task */
        $user = User::factory()->create();
        $this->runTestsPUT("api/users/{$user->id}");
    }

    /**
     * DESTROY
     * @return void
     */
    public function testDeleteUsers(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        /** @var User $task */
        $user = User::factory()->create();
        $this->runTestsDELETE("api/users/{$user->id}");
    }
}
