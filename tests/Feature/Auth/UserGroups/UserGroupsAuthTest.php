<?php

namespace Tests\Feature\Auth\UserGroups;

use App\Enums\Roles;
use App\Models\UserGroups\UserGroup;
use Tests\Feature\Auth\AuthTestCase;

class UserGroupsAuthTest extends AuthTestCase
{
    /**
     * INDEX
     */
    public function test_get_user_groups_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        $this->runTestsGET('api/user-groups');
    }

    /**
     * INDEX
     */
    public function test_get_user_groups_no_task_role_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], [Roles::TASK_ROLE]);
        $this->runTestsGET('api/user-groups');
    }

    /**
     * STORE
     */
    public function test_post_task_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        $this->runTestsPOST('api/user-groups');
    }

    /**
     * STORE
     */
    public function test_post_task_no_task_role_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], [Roles::TASK_ROLE]);
        $this->runTestsPOST('api/user-groups');
    }

    /**
     * SHOW
     */
    public function test_get_family_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        /** @var UserGroup $userGroup */
        $userGroup = UserGroup::factory()->create();
        $this->runTestsGET("api/user-groups/{$userGroup->id}");
    }

    /**
     * UPDATE
     */
    public function test_put_task_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        /** @var UserGroup $userGroup */
        $userGroup = UserGroup::factory()->create();
        $this->runTestsPUT("api/user-groups/{$userGroup->id}");
    }

    /**
     * DESTROY
     */
    public function test_delete_task_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        /** @var UserGroup $userGroup */
        $userGroup = UserGroup::factory()->create();
        $this->runTestsDELETE("api/user-groups/{$userGroup->id}");
    }

    /**
     * DESTROY
     */
    public function test_delete_task_no_task_role_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], [Roles::TASK_ROLE]);
        /** @var UserGroup $userGroup */
        $userGroup = UserGroup::factory()->create();
        $this->runTestsDELETE("api/user-groups/{$userGroup->id}");
    }
}
