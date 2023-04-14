<?php

namespace Tests\Feature\Auth\Tasks;

use App\Enums\Roles;
use App\Models\Tasks\Family;
use Tests\Feature\Auth\AuthTestCase;

class FamiliesAuthTest extends AuthTestCase
{
    /**
     * INDEX
     * @return void
     */
    public function test_get_families_user_permissions(): void
    {
        $this->initRoles([Roles::USERS_ROLE], []);
        $this->runTestsGET('api/families');
    }

    /**
     * INDEX
     * @return void
     */
    public function test_get_families_no_task_role_user_permissions(): void
    {
        $this->initRoles([Roles::USERS_ROLE], [Roles::TASK_ROLE]);
        $this->runTestsGET('api/families');
    }

    /**
     * STORE
     * @return void
     */
    public function test_post_task_user_permissions(): void
    {
        $this->initRoles([Roles::USERS_ROLE], []);
        $this->runTestsPOST('api/families');
    }

    /**
     * STORE
     * @return void
     */
    public function test_post_task_no_task_role_user_permissions(): void
    {
        $this->initRoles([Roles::USERS_ROLE], [Roles::TASK_ROLE]);
        $this->runTestsPOST('api/families');
    }

    /**
     * SHOW
     * @return void
     */
    public function test_get_family_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        /** @var Family $family */
        $family = Family::factory()->create();
        $this->runTestsGET("api/families/{$family->id}");
    }

    /**
     * UPDATE
     * @return void
     */
    public function test_put_task_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        /** @var Family $family */
        $family = Family::factory()->create();
        $this->runTestsPUT("api/families/{$family->id}");
    }

    /**
     * DESTROY
     * @return void
     */
    public function test_delete_task_user_permissions(): void
    {
        $this->initRoles([Roles::USERS_ROLE], []);
        /** @var Family $family */
        $family = Family::factory()->create();
        $this->runTestsDELETE("api/families/{$family->id}");
    }

    /**
     * DESTROY
     * @return void
     */
    public function test_delete_task_no_task_role_user_permissions(): void
    {
        $this->initRoles([Roles::USERS_ROLE], [Roles::TASK_ROLE]);
        /** @var Family $family */
        $family = Family::factory()->create();
        $this->runTestsDELETE("api/families/{$family->id}");
    }
}
