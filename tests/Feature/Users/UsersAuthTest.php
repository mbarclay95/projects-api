<?php

namespace Tests\Feature\Users;

use App\Enums\Roles;
use App\Models\Users\User;
use Tests\Feature\Auth\AuthTestCase;

class UsersAuthTest extends AuthTestCase
{
    /**
     * INDEX
     *
     * @return void
     */
    public function test_get_users()
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        $this->runTestsGET('api/users');
    }

    /**
     * STORE
     */
    public function test_post_users(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        $this->runTestsPOST('api/users');
    }

    /**
     * UPDATE
     */
    public function test_put_users(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        /** @var User $task */
        $user = User::factory()->create();
        $this->runTestsPUT("api/users/{$user->id}");
    }

    /**
     * DESTROY
     */
    public function test_delete_users(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        /** @var User $task */
        $user = User::factory()->create();
        $this->runTestsDELETE("api/users/{$user->id}");
    }
}
