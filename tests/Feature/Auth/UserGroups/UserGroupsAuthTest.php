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
    public function test_get_families_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        $this->runTestsGET('api/families');
    }

    /**
     * INDEX
     */
    public function test_get_families_no_task_role_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], [Roles::TASK_ROLE]);
        $this->runTestsGET('api/families');
    }

    /**
     * STORE
     */
    public function test_post_task_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        $this->runTestsPOST('api/families');
    }

    /**
     * STORE
     */
    public function test_post_task_no_task_role_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], [Roles::TASK_ROLE]);
        $this->runTestsPOST('api/families');
    }

    /**
     * SHOW
     */
    public function test_get_family_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        /** @var UserGroup $userGroup */
        $userGroup = UserGroup::factory()->create();
        $this->runTestsGET("api/families/{$userGroup->id}");
    }

    /**
     * UPDATE
     */
    public function test_put_task_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        /** @var UserGroup $userGroup */
        $userGroup = UserGroup::factory()->create();
        $this->runTestsPUT("api/families/{$userGroup->id}");
    }

    /**
     * DESTROY
     */
    public function test_delete_task_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], []);
        /** @var UserGroup $userGroup */
        $userGroup = UserGroup::factory()->create();
        $this->runTestsDELETE("api/families/{$userGroup->id}");
    }

    /**
     * DESTROY
     */
    public function test_delete_task_no_task_role_user_permissions(): void
    {
        $this->initRoles([Roles::ADMIN_ROLE], [Roles::TASK_ROLE]);
        /** @var UserGroup $userGroup */
        $userGroup = UserGroup::factory()->create();
        $this->runTestsDELETE("api/families/{$userGroup->id}");
    }
}
