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

    public function test_total_rounds_must_be_at_least_one(): void
    {
        $this->patchDraft(['totalRounds' => 0])->assertStatus(422);
        self::assertEquals(2, $this->draft->fresh()->total_rounds);
    }

    /**
     * DraftService::currentRound() is what makes this real: with one member
     * and one pick already made, the draft is into round 2, so dropping back
     * to a single round would end it retroactively mid-round.
     */
    public function test_total_rounds_cannot_drop_below_the_current_round(): void
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

    /**
     * The other side of the guard above. Once every pick is in,
     * currentRound() reads total_rounds + 1, so comparing an unchanged
     * total_rounds against it rejected every edit to a completed draft — a
     * rename included — citing rounds the organizer never touched.
     */
    public function test_a_completed_draft_can_still_be_edited(): void
    {
        $this->completeTheDraft();

        $this->patchDraft(['name' => 'Renamed After Completion'])->assertSuccessful();

        $fresh = $this->draft->fresh();
        self::assertEquals('Renamed After Completion', $fresh->name);
        self::assertEquals(2, $fresh->total_rounds);
    }

    public function test_max_participants_cannot_drop_below_the_current_member_count(): void
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
    public function test_in_progress_requires_a_fully_ordered_roster(): void
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

    public function test_a_picked_team_cannot_be_deleted(): void
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

    public function test_a_member_with_picks_cannot_be_deleted(): void
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
     * One member over the draft's two rounds, so pickCount reaches
     * memberCount * total_rounds and the draft is genuinely complete rather
     * than just labelled that way.
     */
    private function completeTheDraft(): void
    {
        $member = DraftMember::factory()->create(['draft_id' => $this->draft->id, 'pick_position' => 1]);
        foreach ([1, 2] as $pickNumber) {
            $team = DraftTeam::factory()->create(['draft_id' => $this->draft->id]);
            DraftPick::factory()->create([
                'draft_id' => $this->draft->id,
                'draft_member_id' => $member->id,
                'draft_team_id' => $team->id,
                'pick_number' => $pickNumber,
            ]);
        }

        $this->draft->status = DraftStatus::COMPLETE;
        $this->draft->save();
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
            'authorization' => 'Bearer '.$token,
        ]);
    }

    private function deleteAs(string $uri): TestResponse
    {
        $token = auth()->login($this->admin);

        return $this->actingAs($this->admin)->json('DELETE', $uri, [], [
            'accept' => 'application/json',
            'authorization' => 'Bearer '.$token,
        ]);
    }
}
