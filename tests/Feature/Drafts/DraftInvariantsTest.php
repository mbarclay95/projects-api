<?php

namespace Tests\Feature\Drafts;

use App\Enums\Roles;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Drafts\DraftPick;
use App\Models\Drafts\DraftTeam;
use App\Models\Users\User;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class DraftInvariantsTest extends TestCase
{
    private User $admin;

    private Draft $draft;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole(Roles::DRAFTS_ROLE);
        $this->draft = Draft::createEntity(['name' => 'Invariants Draft', 'totalRounds' => 2], $this->admin);
    }

    public function testTotalRoundsMustBeAtLeastOne(): void
    {
        $this->patchDraft(['totalRounds' => 0])->assertStatus(422);
        self::assertEquals(2, $this->draft->fresh()->total_rounds);
    }

    /**
     * DraftService::currentRound() is what makes this real: with one member
     * and one pick already made, the draft is into round 2, so dropping back
     * to a single round would end it retroactively mid-round.
     */
    public function testTotalRoundsCannotDropBelowTheCurrentRound(): void
    {
        $member = DraftMember::factory()->create(['draft_id' => $this->draft->id, 'pick_position' => 1]);
        $team = DraftTeam::factory()->create(['draft_id' => $this->draft->id]);
        DraftPick::factory()->create([
            'draft_id' => $this->draft->id,
            'draft_member_id' => $member->id,
            'draft_team_id' => $team->id,
            'pick_number' => 1,
        ]);

        $this->patchDraft(['totalRounds' => 1])->assertStatus(422);
        self::assertEquals(2, $this->draft->fresh()->total_rounds);
    }

    public function testMaxParticipantsCannotDropBelowTheCurrentMemberCount(): void
    {
        DraftMember::factory()->count(3)->create(['draft_id' => $this->draft->id]);

        $this->patchDraft(['maxParticipants' => 2])->assertStatus(422);
        self::assertNull($this->draft->fresh()->max_participants);
    }

    /**
     * DraftService::memberCount() only counts ordered members, so an
     * unordered roster reads as zero members and the draft would report
     * itself complete before a single pick — see admin-crud.md.
     */
    public function testInProgressRequiresAFullyOrderedRoster(): void
    {
        $memberA = DraftMember::factory()->create(['draft_id' => $this->draft->id]);
        $memberB = DraftMember::factory()->create(['draft_id' => $this->draft->id]);

        $this->patchDraft(['status' => 'in_progress'])->assertStatus(422);
        self::assertNotEquals('in_progress', $this->draft->fresh()->status->value);

        $memberA->update(['pick_position' => 1]);
        $memberB->update(['pick_position' => 2]);

        $this->patchDraft(['status' => 'in_progress'])
             ->assertSuccessful()
             ->assertJsonFragment(['status' => 'in_progress']);
    }

    public function testAPickedTeamCannotBeDeleted(): void
    {
        $member = DraftMember::factory()->create(['draft_id' => $this->draft->id, 'pick_position' => 1]);
        $team = DraftTeam::factory()->create(['draft_id' => $this->draft->id]);
        DraftPick::factory()->create([
            'draft_id' => $this->draft->id,
            'draft_member_id' => $member->id,
            'draft_team_id' => $team->id,
        ]);

        $this->deleteAs("api/draft-teams/{$team->id}")->assertStatus(422);
        self::assertNotNull($team->fresh());
    }

    public function testAMemberWithPicksCannotBeDeleted(): void
    {
        $member = DraftMember::factory()->create(['draft_id' => $this->draft->id, 'pick_position' => 1]);
        $team = DraftTeam::factory()->create(['draft_id' => $this->draft->id]);
        DraftPick::factory()->create([
            'draft_id' => $this->draft->id,
            'draft_member_id' => $member->id,
            'draft_team_id' => $team->id,
        ]);

        $this->deleteAs("api/draft-members/{$member->id}")->assertStatus(422);
        self::assertNotNull($member->fresh());
    }

    /**
     * Every field a real edit form would submit, so an assertion here only
     * ever exercises the one invariant under test rather than tripping over
     * an omitted field falling back to its create-time default.
     */
    private function patchDraft(array $overrides): TestResponse
    {
        $data = array_merge([
            'name' => $this->draft->name,
            'totalRounds' => $this->draft->total_rounds,
            'maxParticipants' => $this->draft->max_participants,
        ], $overrides);

        $token = auth()->login($this->admin);

        return $this->actingAs($this->admin)->json('PUT', "api/drafts/{$this->draft->id}", $data, [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $token,
        ]);
    }

    private function deleteAs(string $uri): TestResponse
    {
        $token = auth()->login($this->admin);

        return $this->actingAs($this->admin)->json('DELETE', $uri, [], [
            'accept' => 'application/json',
            'authorization' => 'Bearer ' . $token,
        ]);
    }
}
