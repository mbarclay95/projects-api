<?php

namespace Tests\Feature\Tags;

use App\Enums\Roles;
use App\Models\Tags\Tag;
use App\Models\Tasks\Task;
use App\Models\UserGroups\UserGroup;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TagScopeTest extends TestCase
{
    private User $user;

    private UserGroup $family;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::TASK_ROLE);
        $this->family = UserGroup::factory()->create();
        $this->family->members()->attach($this->user->id, ['scope' => 'tasks']);
    }

    public function test_the_tasks_scope_returns_tags_on_tasks_the_caller_can_see(): void
    {
        $userTask = Task::factory()->create([
            'owner_type' => (new User)->getMorphClass(),
            'owner_id' => $this->user->id,
        ]);
        $userTask->updateTags(['kitchen']);

        $familyTask = Task::factory()->create([
            'owner_type' => (new UserGroup)->getMorphClass(),
            'owner_id' => $this->family->id,
        ]);
        $familyTask->updateTags(['yard']);

        $tags = $this->jsonAs($this->user, 'GET', 'api/tags?scope=tasks')
            ->assertSuccessful()
            ->json();

        self::assertContains('kitchen', $tags);
        self::assertContains('yard', $tags);
    }

    public function test_the_tasks_scope_excludes_tags_on_another_users_tasks(): void
    {
        $stranger = User::factory()->create();
        $strangerTask = Task::factory()->create([
            'owner_type' => (new User)->getMorphClass(),
            'owner_id' => $stranger->id,
        ]);
        $strangerTask->updateTags(['stranger']);

        $tags = $this->jsonAs($this->user, 'GET', 'api/tags?scope=tasks')
            ->assertSuccessful()
            ->json();

        self::assertNotContains('stranger', $tags);
    }

    public function test_a_tag_attached_to_nothing_is_not_in_the_tasks_scope(): void
    {
        Tag::create(['tag' => 'orphan']);

        $tags = $this->jsonAs($this->user, 'GET', 'api/tags?scope=tasks')
            ->assertSuccessful()
            ->json();

        self::assertNotContains('orphan', $tags);
    }

    public function test_a_request_with_no_scope_or_an_unknown_scope_is_rejected(): void
    {
        $this->jsonAs($this->user, 'GET', 'api/tags')->assertStatus(422);
        $this->jsonAs($this->user, 'GET', 'api/tags?scope=groceries')->assertStatus(422);
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
