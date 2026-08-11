<?php

namespace Tests\Feature\Drafts;

use App\Enums\DraftStatus;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Drafts\DraftPick;
use App\Models\Drafts\DraftTeam;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * `POST public/draft-picks` — turn enforcement, the race, auto-complete.
 */
class PublicDraftPickTest extends TestCase
{
    public function testOutOfTurnIsRejected(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 1]);
        DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        $memberTwo = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 2]);
        $team = DraftTeam::factory()->create(['draft_id' => $draft->id]);

        $this->pick($draft, $memberTwo, $team)->assertStatus(422);
        self::assertEquals(0, $draft->draftPicks()->count());
    }

    public function testATakenTeamIsRejected(): void
    {
        // Two rounds, one member: the second round puts the same member back
        // on the clock, now attempting the team it already holds.
        $draft = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 2]);
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        $team = DraftTeam::factory()->create(['draft_id' => $draft->id]);
        DraftTeam::factory()->create(['draft_id' => $draft->id]);
        DraftPick::factory()->create([
            'draft_id' => $draft->id,
            'draft_member_id' => $member->id,
            'draft_team_id' => $team->id,
            'pick_number' => 1,
        ]);

        $this->pick($draft, $member, $team)->assertStatus(422);
        self::assertEquals(1, $draft->draftPicks()->count());
    }

    public function testATeamFromAnotherDraftIsRejected(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 1]);
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        $otherDraft = $this->createDraft();
        $foreignTeam = DraftTeam::factory()->create(['draft_id' => $otherDraft->id]);

        $this->pick($draft, $member, $foreignTeam)->assertStatus(422);
        self::assertEquals(0, $draft->draftPicks()->count());
    }

    public function testPickNumberIsIgnoredWhenTheClientSendsOne(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 1]);
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        $team = DraftTeam::factory()->create(['draft_id' => $draft->id]);

        $response = $this->postJson('api/public/draft-picks', [
            'draftId' => $draft->id,
            'token' => $draft->token,
            'secret' => $member->secret,
            'draftTeamId' => $team->id,
            'pickNumber' => 999,
        ])->assertSuccessful();

        self::assertEquals(1, $response->json('pick.pickNumber'));
    }

    public function testAMembersSecretFromDraftACannotPickInDraftB(): void
    {
        $draftA = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 1]);
        $memberA = DraftMember::factory()->create(['draft_id' => $draftA->id, 'pick_position' => 1]);

        $draftB = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 1]);
        DraftMember::factory()->create(['draft_id' => $draftB->id, 'pick_position' => 1]);
        $teamB = DraftTeam::factory()->create(['draft_id' => $draftB->id]);

        $this->postJson('api/public/draft-picks', [
            'draftId' => $draftB->id,
            'token' => $draftB->token,
            'secret' => $memberA->secret,
            'draftTeamId' => $teamB->id,
        ])->assertStatus(404);
        self::assertEquals(0, $draftB->draftPicks()->count());
    }

    public function testTheLastPickOfTheLastRoundFlipsStatusToCompleteAndReturnsIt(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 1]);
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        $team = DraftTeam::factory()->create(['draft_id' => $draft->id]);

        $response = $this->pick($draft, $member, $team)->assertSuccessful();

        self::assertEquals('complete', $response->json('status'));
        self::assertEquals(DraftStatus::COMPLETE, $draft->fresh()->status);
    }

    /**
     * A single PHP process and one DB connection can't produce two truly
     * overlapping requests, so the race is simulated with a second,
     * independent connection to the same database — its insert commits on
     * its own, unaffected by this request's transaction, standing in for a
     * second request whose insert lands between this one's pre-check and its
     * own insert. That connection isn't one RefreshDatabase rolls back, so
     * its row is deleted by hand in the `finally` block.
     */
    public function testTwoPicksRacingTheSameTeamOneSucceedsOneGetsTheFriendly422(): void
    {
        $draft = $this->createDraft(['status' => DraftStatus::IN_PROGRESS, 'total_rounds' => 1]);
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        $team = DraftTeam::factory()->create(['draft_id' => $draft->id]);

        config(['database.connections.race' => config('database.connections.' . config('database.default'))]);

        DraftPick::saving(function () use ($draft, $member, $team) {
            DB::connection('race')->table('draft_picks')->insert([
                'draft_id' => $draft->id,
                'draft_member_id' => $member->id,
                'draft_team_id' => $team->id,
                'pick_number' => 1,
                'made_by_admin' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        try {
            $this->pick($draft, $member, $team)
                 ->assertStatus(422)
                 ->assertJsonPath('errors.draftTeamId.0', 'That team was just taken.');

            self::assertEquals(1, $draft->draftPicks()->count());
        } finally {
            DraftPick::flushEventListeners();
            DB::connection('race')->table('draft_picks')->where('draft_id', $draft->id)->delete();
            DB::purge('race');
        }
    }

    private function createDraft(array $overrides = []): Draft
    {
        return Draft::factory()->create(array_merge(
            ['created_by_id' => User::factory()->create()->id],
            $overrides
        ));
    }

    private function pick(Draft $draft, DraftMember $member, DraftTeam $team)
    {
        return $this->postJson('api/public/draft-picks', [
            'draftId' => $draft->id,
            'token' => $draft->token,
            'secret' => $member->secret,
            'draftTeamId' => $team->id,
        ]);
    }
}
