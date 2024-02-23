<?php

namespace Tests\Feature\Auth\Tasks;

use App\Enums\Roles;
use App\Models\Tasks\Task;
use App\Models\Users\User;
use Tests\Feature\Auth\AuthTestCase;

class TaskHistoriesAuthTest extends AuthTestCase
{
    /**
     * INDEX
     * @return void
     */
    public function test_get_task_histories_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], []);
        /** @var Task $task */
        $task = Task::factory()->create([
            'owner_type' => User::class,
            'owner_id' => $this->goodUser->id
        ]);
        $this->runTestsGET("api/tasks/{$task->id}/history");
    }

    /**
     * INDEX
     * @return void
     */
    public function test_get_task_histories_not_users_task_user_permissions(): void
    {
        $this->initRoles([Roles::TASK_ROLE], [Roles::TASK_ROLE]);
        /** @var Task $task */
        $task = Task::factory()->create([
            'owner_type' => User::class,
            'owner_id' => $this->goodUser->id
        ]);
        $this->runTestsGET("api/tasks/{$task->id}/history");
    }
}
