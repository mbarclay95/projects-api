<?php

namespace Tests\Unit\Drafts;

use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Drafts\DraftPick;
use App\Models\Drafts\DraftTeam;
use App\Models\Users\User;
use App\Services\Drafts\DraftService;
use Tests\TestCase;

class DraftServiceTest extends TestCase
{
    /**
     * Three members over two rounds — six picks. Walks every slot, so the
     * round boundary at P % N == 0 and the transition past the last pick are
     * both crossed rather than sampled.
     */
    public function testWalkingADraftFromStartToFinish()
    {
        $draft = $this->createDraft(memberCount: 3, totalRounds: 2);

        //-------------------------------------------------
        // no picks yet — first member of the first round
        self::assertEquals(1, $this->positionOnTheClock($draft));
        self::assertEquals(1, DraftService::currentRound($draft));
        self::assertEquals(1, DraftService::nextPickNumber($draft));
        self::assertFalse(DraftService::isComplete($draft));
        //-------------------------------------------------

        //-------------------------------------------------
        // mid-round — the case where the formula is obviously right
        $this->addPicks($draft, 1);
        self::assertEquals(2, $this->positionOnTheClock($draft));
        self::assertEquals(1, DraftService::currentRound($draft));
        self::assertEquals(2, DraftService::nextPickNumber($draft));
        self::assertFalse(DraftService::isComplete($draft));
        //-------------------------------------------------

        //-------------------------------------------------
        // last slot of round one
        $this->addPicks($draft, 1);
        self::assertEquals(3, $this->positionOnTheClock($draft));
        self::assertEquals(1, DraftService::currentRound($draft));
        //-------------------------------------------------

        //-------------------------------------------------
        // P % N == 0 — wraps to position 1 and ticks the round over
        $this->addPicks($draft, 1);
        self::assertEquals(1, $this->positionOnTheClock($draft));
        self::assertEquals(2, DraftService::currentRound($draft));
        self::assertEquals(4, DraftService::nextPickNumber($draft));
        self::assertFalse(DraftService::isComplete($draft));
        //-------------------------------------------------

        //-------------------------------------------------
        // the last pick of the draft is still owed
        $this->addPicks($draft, 2);
        self::assertEquals(3, $this->positionOnTheClock($draft));
        self::assertEquals(2, DraftService::currentRound($draft));
        self::assertEquals(6, DraftService::nextPickNumber($draft));
        self::assertFalse(DraftService::isComplete($draft));
        //-------------------------------------------------

        //-------------------------------------------------
        // past the end — nobody is on the clock
        $this->addPicks($draft, 1);
        self::assertNull(DraftService::onTheClock($draft));
        self::assertTrue(DraftService::isComplete($draft));
        self::assertEquals(7, DraftService::nextPickNumber($draft));
        // currentRound keeps counting past total_rounds rather than clamping,
        // so callers must check isComplete() before displaying it.
        self::assertEquals(3, DraftService::currentRound($draft));
        //-------------------------------------------------
    }

    /**
     * total_rounds defaults to 1, which is the "everyone picks one team" draft.
     */
    public function testASingleRoundDraftEndsAfterOnePassOfTheRoster()
    {
        $draft = $this->createDraft(memberCount: 4, totalRounds: 1);

        $this->addPicks($draft, 3);
        self::assertEquals(4, $this->positionOnTheClock($draft));
        self::assertEquals(1, DraftService::currentRound($draft));
        self::assertFalse(DraftService::isComplete($draft));

        $this->addPicks($draft, 1);
        self::assertNull(DraftService::onTheClock($draft));
        self::assertTrue(DraftService::isComplete($draft));
    }

    /**
     * A draft still in signup has a roster but no order. Nothing should be
     * divided by that zero.
     */
    public function testADraftWithNoOrderedMembersIsNotOnAnybodysClock()
    {
        $draft = $this->createDraft(memberCount: 0, totalRounds: 3);
        DraftMember::factory()->count(2)->create(['draft_id' => $draft->id]);

        self::assertNull(DraftService::onTheClock($draft));
        self::assertTrue(DraftService::isComplete($draft));
        self::assertEquals(1, DraftService::currentRound($draft));
        self::assertEquals(1, DraftService::nextPickNumber($draft));
    }

    /**
     * Someone claiming a name mid-draft must not lengthen the rotation. If an
     * unordered member counted toward N, the clock would advance to a position
     * nobody holds and the draft would wedge.
     */
    public function testAnUnorderedMemberDoesNotLengthenTheRotation()
    {
        $draft = $this->createDraft(memberCount: 2, totalRounds: 1);
        DraftMember::factory()->create(['draft_id' => $draft->id, 'pick_position' => null]);

        $this->addPicks($draft, 1);
        self::assertEquals(2, $this->positionOnTheClock($draft));

        $this->addPicks($draft, 1);
        self::assertNull(DraftService::onTheClock($draft));
        self::assertTrue(DraftService::isComplete($draft));
    }

    /**
     * Picks in another draft must not move this draft's clock.
     */
    public function testTheClockIsScopedToItsOwnDraft()
    {
        $draft = $this->createDraft(memberCount: 2, totalRounds: 1);
        $otherDraft = $this->createDraft(memberCount: 2, totalRounds: 1);
        $this->addPicks($otherDraft, 2);

        self::assertEquals(1, $this->positionOnTheClock($draft));
        self::assertFalse(DraftService::isComplete($draft));
        self::assertTrue(DraftService::isComplete($otherDraft));
    }

    private function positionOnTheClock(Draft $draft): ?int
    {
        return DraftService::onTheClock($draft)?->pick_position;
    }

    private function createDraft(int $memberCount, int $totalRounds): Draft
    {
        $draft = Draft::factory()->create([
            'created_by_id' => User::factory()->create()->id,
            'total_rounds' => $totalRounds,
        ]);

        for ($position = 1; $position <= $memberCount; $position++) {
            DraftMember::factory()->create([
                'draft_id' => $draft->id,
                'pick_position' => $position,
            ]);
        }

        return $draft;
    }

    /**
     * Attribution is deliberately arbitrary — the derivation counts picks, not
     * who made them.
     */
    private function addPicks(Draft $draft, int $count): void
    {
        $member = $draft->draftMembers()->orderBy('pick_position')->first();

        for ($i = 0; $i < $count; $i++) {
            DraftPick::factory()->create([
                'draft_id' => $draft->id,
                'draft_member_id' => $member->id,
                'draft_team_id' => DraftTeam::factory()->create(['draft_id' => $draft->id])->id,
                'pick_number' => $draft->draftPicks()->count() + 1,
            ]);
        }
    }
}
