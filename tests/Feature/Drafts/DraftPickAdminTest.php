<?php

namespace Tests\Feature\Drafts;

use App\Enums\DraftStatus;
use App\Enums\Roles;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Drafts\DraftPick;
use App\Models\Drafts\DraftTeam;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

/**
 * `DraftPickController` — the admin's in-draft controls: pick for whoever is
 * on the clock, correct a mis-click, undo. See in-draft-control.md.
 */
class DraftPickAdminTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Roles::DRAFTS_ROLE);
    }

    public function testAnAdminPickAttributesToTheMemberOnTheClockAndSetsMadeByAdmin(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 1]);
        $onTheClock = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 2]);
        $team = DraftTeam::factory()->create(['draft_id' => $draft->id]);

        $response = $this->jsonAs($this->admin, 'POST', 'api/draft-picks', [
            'draftId' => $draft->id,
            'draftTeamId' => $team->id,
        ])->assertSuccessful();

        self::assertEquals($onTheClock->id, $response->json('pick.draftMemberId'));
        self::assertTrue($response->json('pick.madeByAdmin'));
    }

    public function testAnAdminPickOnTheFinalSlotAutoCompletesTheDraft(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 1]);
        DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        $team = DraftTeam::factory()->create(['draft_id' => $draft->id]);

        $response = $this->jsonAs($this->admin, 'POST', 'api/draft-picks', [
            'draftId' => $draft->id,
            'draftTeamId' => $team->id,
        ])->assertSuccessful();

        self::assertEquals('complete', $response->json('status'));
        self::assertEquals(DraftStatus::COMPLETE, $draft->fresh()->status);
    }

    public function testCorrectingAPickCannotTakeATeamAnotherPickAlreadyHoldsAndLeavesTheRestAlone(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 2]);
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        $teamA = DraftTeam::factory()->create(['draft_id' => $draft->id]);
        $teamB = DraftTeam::factory()->create(['draft_id' => $draft->id]);
        $teamC = DraftTeam::factory()->create(['draft_id' => $draft->id]);
        $pick = DraftPick::factory()->create([
            'draft_id' => $draft->id,
            'draft_member_id' => $member->id,
            'draft_team_id' => $teamA->id,
            'pick_number' => 1,
            'made_by_admin' => false,
        ]);
        DraftPick::factory()->create([
            'draft_id' => $draft->id,
            'draft_member_id' => $member->id,
            'draft_team_id' => $teamB->id,
            'pick_number' => 2,
            'made_by_admin' => false,
        ]);

        $this->jsonAs($this->admin, 'PUT', "api/draft-picks/{$pick->id}", ['draftTeamId' => $teamB->id])
             ->assertStatus(422);
        self::assertEquals($teamA->id, $pick->fresh()->draft_team_id);

        $response = $this->jsonAs($this->admin, 'PUT', "api/draft-picks/{$pick->id}", ['draftTeamId' => $teamC->id])
                          ->assertSuccessful();

        self::assertEquals($teamC->id, $response->json('pick.draftTeamId'));
        self::assertEquals(1, $response->json('pick.pickNumber'));
        self::assertEquals($member->id, $response->json('pick.draftMemberId'));
        self::assertFalse($response->json('pick.madeByAdmin'));
    }

    public function testUndoingANonFinalPickIs422(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 2]);
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        $teamA = DraftTeam::factory()->create(['draft_id' => $draft->id]);
        $teamB = DraftTeam::factory()->create(['draft_id' => $draft->id]);
        $firstPick = DraftPick::factory()->create([
            'draft_id' => $draft->id,
            'draft_member_id' => $member->id,
            'draft_team_id' => $teamA->id,
            'pick_number' => 1,
        ]);
        DraftPick::factory()->create([
            'draft_id' => $draft->id,
            'draft_member_id' => $member->id,
            'draft_team_id' => $teamB->id,
            'pick_number' => 2,
        ]);

        $this->jsonAs($this->admin, 'DELETE', "api/draft-picks/{$firstPick->id}")->assertStatus(422);
        self::assertEquals(2, $draft->draftPicks()->count());
    }

    /**
     * Decision 6, proven end to end: the draft auto-completes on the last
     * pick, undo reopens it, and a participant can then pick into the slot
     * the undone pick vacated.
     */
    public function testUndoingTheFinalPickOfACompleteDraftReopensItForParticipantPicking(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 1]);
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        $team = DraftTeam::factory()->create(['draft_id' => $draft->id]);

        $pickResponse = $this->jsonAs($this->admin, 'POST', 'api/draft-picks', [
            'draftId' => $draft->id,
            'draftTeamId' => $team->id,
        ])->assertSuccessful();
        self::assertEquals('complete', $pickResponse->json('status'));
        $pickId = $pickResponse->json('pick.id');

        $undoResponse = $this->jsonAs($this->admin, 'DELETE', "api/draft-picks/{$pickId}")->assertSuccessful();
        self::assertEquals('in_progress', $undoResponse->json('status'));
        self::assertEquals(DraftStatus::IN_PROGRESS, $draft->fresh()->status);
        self::assertNull($undoResponse->json('pick'));

        $this->postJson('api/public/draft-picks', [
            'draftId' => $draft->id,
            'token' => $draft->token,
            'secret' => $member->secret,
            'draftTeamId' => $team->id,
        ])->assertSuccessful();
    }

    private function createDraft(array $overrides = []): Draft
    {
        $draft = Draft::factory()->create(array_merge(
            ['created_by_id' => $this->admin->id],
            $overrides
        ));
        $draft->draftAdmins()->create(['user_id' => $this->admin->id]);

        return $draft;
    }

    private function jsonAs(User $user, string $method, string $uri, array $data = []): TestResponse
    {
        $token = auth()->login($user);

        return $this->actingAs($user)->json($method, $uri, $data, [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $token,
        ]);
    }
}
