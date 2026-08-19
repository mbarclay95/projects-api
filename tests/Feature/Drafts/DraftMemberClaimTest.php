<?php

namespace Tests\Feature\Drafts;

use App\Enums\DraftStatus;
use App\Enums\Roles;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Drafts\DraftTeam;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * `POST public/draft-member-claims` and `DELETE draft-member-claims/{id}` —
 * the two halves of claiming an existing member, per in-draft-control.md.
 */
class DraftMemberClaimTest extends TestCase
{
    public function test_claiming_an_unclaimed_member_issues_a_new_secret_and_retires_the_old(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::SIGNUP]);
        $member = DraftMember::factory()->create([
            'draft_id' => $draft->id,
            'claimed_at' => null,
        ]);
        $oldSecret = $member->secret;

        $response = $this->claim($draft, $member)->assertSuccessful();
        $newSecret = $response->json('secret');

        self::assertNotEquals($oldSecret, $newSecret);
        self::assertNotNull($member->fresh()->claimed_at);

        // The old secret is dead: it can no longer make a pick.
        $team = DraftTeam::factory()->create(['draft_id' => $draft->id]);
        $draft->update(['status' => DraftStatus::IN_PROGRESS]);
        $member->update(['pick_position' => 1]);

        $this->postJson('api/public/draft-picks', [
            'draftId' => $draft->id,
            'token' => $draft->token,
            'secret' => $oldSecret,
            'draftTeamId' => $team->id,
        ])->assertStatus(404);
    }

    public function test_claiming_an_already_claimed_member_is422(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::SIGNUP]);
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'claimed_at' => now()]);

        $this->claim($draft, $member)->assertStatus(422);
    }

    public function test_a_bad_token_is404(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::SIGNUP]);
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'claimed_at' => null]);

        $this->postJson('api/public/draft-member-claims', [
            'draftId' => $draft->id,
            'token' => 'not-the-real-token',
            'draftMemberId' => $member->id,
        ])->assertStatus(404);
    }

    public function test_a_member_id_from_another_draft_is404_indistinguishable_from_a_bad_token(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::SIGNUP]);
        $otherDraft = $this->createDraft();
        $foreignMember = DraftMember::factory()->create(['draft_id' => $otherDraft->id, 'claimed_at' => null]);

        $this->postJson('api/public/draft-member-claims', [
            'draftId' => $draft->id,
            'token' => 'wrong',
            'draftMemberId' => $foreignMember->id,
        ])->assertStatus(404);

        $this->postJson('api/public/draft-member-claims', [
            'draftId' => $draft->id,
            'token' => $draft->token,
            'draftMemberId' => $foreignMember->id,
        ])->assertStatus(404);
    }

    public function test_clearing_a_claim_rotates_the_secret_and_the_old_one_no_longer_picks(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::DRAFTS_ROLE);
        $draft = Draft::createEntity(['name' => 'Clearable'], $admin);
        $member = DraftMember::factory()->create([
            'draft_id' => $draft->id,
            'claimed_at' => now(),
            'pick_position' => 1,
        ]);
        $oldSecret = $member->secret;
        $team = DraftTeam::factory()->create(['draft_id' => $draft->id]);
        $draft->update(['status' => DraftStatus::IN_PROGRESS]);

        $this->jsonAs($admin, 'DELETE', "api/draft-member-claims/{$member->id}")->assertSuccessful();

        self::assertNull($member->fresh()->claimed_at);
        self::assertNotEquals($oldSecret, $member->fresh()->secret);

        $this->postJson('api/public/draft-picks', [
            'draftId' => $draft->id,
            'token' => $draft->token,
            'secret' => $oldSecret,
            'draftTeamId' => $team->id,
        ])->assertStatus(404);
    }

    public function test_a_non_admin_cannot_clear_a_claim_on_a_draft_they_do_not_administer(): void
    {
        $owner = User::factory()->create();
        $owner->assignRole(Roles::DRAFTS_ROLE);
        $stranger = User::factory()->create();
        $stranger->assignRole(Roles::DRAFTS_ROLE);
        $draft = Draft::createEntity(['name' => 'Not Yours'], $owner);
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'claimed_at' => now()]);

        $this->jsonAs($stranger, 'DELETE', "api/draft-member-claims/{$member->id}")->assertUnauthorized();

        self::assertNotNull($member->fresh()->claimed_at);
    }

    private function createDraft(array $overrides = []): Draft
    {
        return Draft::factory()->create(array_merge(
            ['created_by_id' => User::factory()->create()->id],
            $overrides
        ));
    }

    private function claim(Draft $draft, DraftMember $member): TestResponse
    {
        return $this->postJson('api/public/draft-member-claims', [
            'draftId' => $draft->id,
            'token' => $draft->token,
            'draftMemberId' => $member->id,
        ]);
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
