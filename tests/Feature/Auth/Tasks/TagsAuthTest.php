<?php

namespace Tests\Feature\Auth\Tasks;

use App\Enums\Roles;
use Tests\Feature\Auth\AuthTestCase;

class TagsAuthTest extends AuthTestCase
{
    /**
     * INDEX
     * @return void
     */
    public function test_get_tags_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        $this->runTestsGET('api/tags');
    }
}
