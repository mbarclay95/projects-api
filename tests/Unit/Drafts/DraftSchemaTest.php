<?php

namespace Tests\Unit\Drafts;

use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Drafts\DraftPick;
use App\Models\Drafts\DraftTeam;
use App\Models\Users\User;
use Illuminate\Database\QueryException;
use Tests\TestCase;

/**
 * The composite unique indexes are the concurrency backstop behind the pick
 * endpoint, not tidiness. Each test here triggers a real constraint violation,
 * so it must be the last database work in its test — Postgres aborts the
 * surrounding transaction once a statement fails.
 */
class DraftSchemaTest extends TestCase
{
    /**
     * Postgres treats NULLs as distinct in a unique index, which is what lets a
     * whole roster sit unordered during signup under
     * unique(draft_id, pick_position). A future tidy-up of the indexes would
     * break this without noticing.
     */
    public function testManyMembersMayHaveNoPickPositionInOneDraft()
    {
        $draft = $this->createDraft();

        DraftMember::factory()->count(4)->create(['draft_id' => $draft->id, 'pick_position' => null]);

        self::assertEquals(4, $draft->draftMembers()->whereNull('pick_position')->count());
    }

    public function testTwoMembersCannotShareAPickPosition()
    {
        $draft = $this->createDraft();
        DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 3]);

        $this->assertUniqueViolation(fn () => DraftMember::factory()->create([
            'draft_id' => $draft->id,
            'pick_position' => 3,
        ]));
    }

    /**
     * The same position in a different draft is fine — the index is composite,
     * not global.
     */
    public function testTheSamePickPositionIsFineInAnotherDraft()
    {
        $draft = $this->createDraft();
        $otherDraft = $this->createDraft();

        DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        DraftMember::factory()->create(['draft_id' => $otherDraft->id, 'pick_position' => 1]);

        self::assertEquals(1, $draft->draftMembers()->count());
        self::assertEquals(1, $otherDraft->draftMembers()->count());
    }

    /**
     * unique(draft_id, draft_team_id) drawn out: a team goes exactly once. This
     * is the 23505 stage 4 catches and turns into "that team was just taken"
     * rather than a 500.
     */
    public function testATeamCannotBePickedTwice()
    {
        $draft = $this->createDraft();
        $team = DraftTeam::factory()->create(['draft_id' => $draft->id]);
        $memberOne = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);
        $memberTwo = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 2]);

        DraftPick::factory()->create([
            'draft_id' => $draft->id,
            'draft_member_id' => $memberOne->id,
            'draft_team_id' => $team->id,
            'pick_number' => 1,
        ]);

        $this->assertUniqueViolation(fn () => DraftPick::factory()->create([
            'draft_id' => $draft->id,
            'draft_member_id' => $memberTwo->id,
            'draft_team_id' => $team->id,
            'pick_number' => 2,
        ]));
    }

    /**
     * unique(draft_id, pick_number) — the other half of the pick race, where
     * two writers land in the same slot with different teams.
     */
    public function testTwoPicksCannotShareASlot()
    {
        $draft = $this->createDraft();
        $member = DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => 1]);

        DraftPick::factory()->create([
            'draft_id' => $draft->id,
            'draft_member_id' => $member->id,
            'draft_team_id' => DraftTeam::factory()->create(['draft_id' => $draft->id])->id,
            'pick_number' => 1,
        ]);

        $this->assertUniqueViolation(fn () => DraftPick::factory()->create([
            'draft_id' => $draft->id,
            'draft_member_id' => $member->id,
            'draft_team_id' => DraftTeam::factory()->create(['draft_id' => $draft->id])->id,
            'pick_number' => 1,
        ]));
    }

    public function testTwoDraftsCannotShareAToken()
    {
        $draft = $this->createDraft();

        $this->assertUniqueViolation(fn () => Draft::factory()->create([
            'created_by_id' => User::factory()->create()->id,
            'token' => $draft->token,
        ]));
    }

    /**
     * Asserts the SQLSTATE rather than merely that something threw, since
     * 23505 is the specific code the pick endpoint will branch on.
     */
    private function assertUniqueViolation(callable $insert): void
    {
        try {
            $insert();
            self::fail('expected a unique constraint violation, but the insert succeeded');
        } catch (QueryException $exception) {
            self::assertEquals('23505', $exception->getCode());
        }
    }

    private function createDraft(): Draft
    {
        return Draft::factory()->create([
            'created_by_id' => User::factory()->create()->id,
        ]);
    }
}
