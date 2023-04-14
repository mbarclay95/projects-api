<?php

namespace Tests\Feature\Auth\Tasks;

use App\Enums\Roles;
use App\Models\Tasks\Task;
use Tests\Feature\Auth\AuthTestCase;

class TasksAuthTest extends AuthTestCase
{
    /**
     * INDEX
     * @return void
     */
    public function test_get_tasks_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        $this->runTestsGET('api/tasks');
    }

    /**
     * STORE
     * @return void
     */
    public function test_post_task_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        $this->runTestsPOST('api/tasks');
    }

    /**
     * UPDATE
     * @return void
     */
    public function test_put_task_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        /** @var Task $task */
        $task = Task::factory()->create();
        $this->runTestsPUT("api/tasks/{$task->id}");
    }

    /**
     * DESTROY
     * @return void
     */
    public function test_delete_task_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        /** @var Task $task */
        $task = Task::factory()->create();
        $this->runTestsDELETE("api/tasks/{$task->id}");
    }
}
