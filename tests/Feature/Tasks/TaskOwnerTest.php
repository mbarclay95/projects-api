<?php

namespace Tests\Feature\Tasks;

use App\Enums\Roles;
use App\Models\Tasks\Family;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskUserConfig;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TaskOwnerTest extends TestCase
{
    private User $user;

    private Family $family;

    private Task $familyTask;

    private Task $userTask;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->family = Family::factory()->create();
        TaskUserConfig::factory()->create([
            'user_id' => $this->user->id,
            'family_id' => $this->family->id,
        ]);
        $this->user->assignRole(Roles::TASK_ROLE);

        $this->familyTask = Task::factory()->create([
            'owner_type' => (new Family)->getMorphClass(),
            'owner_id' => $this->family->id,
        ]);
        $this->userTask = Task::factory()->create([
            'owner_type' => (new User)->getMorphClass(),
            'owner_id' => $this->user->id,
        ]);
    }

    public function test_a_family_owned_tasks_owner_is_a_family(): void
    {
        self::assertInstanceOf(Family::class, $this->familyTask->owner);
        self::assertEquals($this->family->id, $this->familyTask->owner->id);
    }

    public function test_a_user_owned_tasks_owner_is_a_user(): void
    {
        self::assertInstanceOf(User::class, $this->userTask->owner);
        self::assertEquals($this->user->id, $this->userTask->owner->id);
    }

    public function test_filtering_by_owner_type_family_returns_only_the_family_task(): void
    {
        $ids = collect($this->jsonAs($this->user, 'GET', 'api/tasks?ownerType=family')
            ->assertSuccessful()
            ->json())->pluck('id');

        self::assertTrue($ids->contains($this->familyTask->id));
        self::assertFalse($ids->contains($this->userTask->id));
    }

    public function test_the_index_reports_owner_type_as_family_or_user(): void
    {
        $byId = collect($this->jsonAs($this->user, 'GET', 'api/tasks')
            ->assertSuccessful()
            ->json())->keyBy('id');

        self::assertEquals('family', $byId[$this->familyTask->id]['ownerType']);
        self::assertEquals('user', $byId[$this->userTask->id]['ownerType']);
    }

    public function test_a_tag_attached_to_a_task_reads_back_through_the_relation(): void
    {
        $this->userTask->updateTags(['groceries']);

        self::assertEquals(['groceries'], $this->userTask->fresh()->tags->pluck('tag')->all());
    }

    public function test_a_user_with_an_assigned_role_still_resolves_its_permissions(): void
    {
        $this->jsonAs($this->user, 'GET', 'api/tasks')->assertSuccessful();

        $stranger = User::factory()->create();
        $this->jsonAs($stranger, 'GET', 'api/tasks')->assertUnauthorized();
    }

    private function jsonAs(User $user, string $method, string $uri, array $data = []): TestResponse
    {
        $token = auth()->login($user);

        return $this->actingAs($user)->json($method, $uri, $data, [
            'accept' => 'application/json',
            'authorization' => 'Bearer '.$token,
        ]);
    }
}
