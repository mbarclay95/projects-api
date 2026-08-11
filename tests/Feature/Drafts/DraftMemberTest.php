<?php

namespace Tests\Feature\Drafts;

use App\Enums\Roles;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * Covers `DraftMember::createEntity()`/`updateEntity()`'s duplicate-name
 * check — the same rule `PublicDraftMemberController::store()` enforces on
 * the public claim flow, added here so an admin adding or renaming a member
 * can't produce the same inconsistency the public side already guards
 * against. See public-draft.md's claim decision.
 */
class DraftMemberTest extends TestCase
{
    private User $admin;

    private Draft $draft;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Roles::DRAFTS_ROLE);
        $this->draft = Draft::createEntity(['name' => 'Member Draft'], $this->admin);
    }

    public function testADuplicateNameIsRejectedOnCreate(): void
    {
        DraftMember::factory()->create(['draft_id' => $this->draft->id, 'name' => 'Mike']);

        $this->postAs('api/draft-members', [
            'draftId' => $this->draft->id,
            'name' => 'Mike',
        ])->assertStatus(422)->assertJsonValidationErrors('name');

        self::assertSame(1, DraftMember::query()->where('draft_id', '=', $this->draft->id)->count());
    }

    public function testADuplicateNameIsRejectedCaseInsensitivelyAndTrimmed(): void
    {
        DraftMember::factory()->create(['draft_id' => $this->draft->id, 'name' => 'Mike']);

        $this->postAs('api/draft-members', [
            'draftId' => $this->draft->id,
            'name' => ' mike ',
        ])->assertStatus(422)->assertJsonValidationErrors('name');
    }

    public function testTheSameNameIsFineInADifferentDraft(): void
    {
        DraftMember::factory()->create(['draft_id' => $this->draft->id, 'name' => 'Mike']);
        $otherDraft = Draft::createEntity(['name' => 'Other Draft'], $this->admin);

        $this->postAs('api/draft-members', [
            'draftId' => $otherDraft->id,
            'name' => 'Mike',
        ])->assertOk();
    }

    public function testRenamingAMemberToAnotherMembersNameIsRejected(): void
    {
        DraftMember::factory()->create(['draft_id' => $this->draft->id, 'name' => 'Mike']);
        $gary = DraftMember::factory()->create(['draft_id' => $this->draft->id, 'name' => 'Gary']);

        $this->putAs("api/draft-members/{$gary->id}", ['name' => 'Mike'])
             ->assertStatus(422)->assertJsonValidationErrors('name');

        self::assertSame('Gary', $gary->fresh()->name);
    }

    public function testRenamingAMemberToItsOwnNameIsFine(): void
    {
        $gary = DraftMember::factory()->create(['draft_id' => $this->draft->id, 'name' => 'Gary']);

        $this->putAs("api/draft-members/{$gary->id}", ['name' => 'Gary'])->assertOk();
    }

    private function postAs(string $uri, array $data): TestResponse
    {
        $token = auth()->login($this->admin);

        return $this->actingAs($this->admin)->json('POST', $uri, $data, [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $token,
        ]);
    }

    private function putAs(string $uri, array $data): TestResponse
    {
        $token = auth()->login($this->admin);

        return $this->actingAs($this->admin)->json('PUT', $uri, $data, [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $token,
        ]);
    }
}
