<?php

namespace Tests\Feature\Auth\Tasks;

use App\Enums\Roles;
use App\Models\Tasks\TaskUserConfig;
use Tests\Feature\Auth\AuthTestCase;

class TaskUserConfigsAuthTest extends AuthTestCase
{
    /**
     * INDEX
     * @return void
     */
    public function test_get_task_user_configs_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        $this->runTestsGET('api/task-user-config');
    }

    /**
     * UPDATE
     * @return void
     */
    public function test_put_task_user_config_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        /** @var TaskUserConfig $config */
        $config = TaskUserConfig::factory()->create();
        $this->runTestsPUT("api/task-user-config/{$config->id}");
    }
}
