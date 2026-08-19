<?php

namespace App\Services\Drafts;

use App\Enums\DraftStatus;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Drafts\DraftPick;
use App\Models\Drafts\DraftTeam;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

/**
 * Whose turn it is, derived rather than stored.
 *
 * Every method counts from the database rather than from loaded relations, so
 * the public pick endpoint can call these inside its lockForUpdate transaction
 * and see the picks another request just inserted.
 */
class DraftService
{
    /**
     * The tail shared by every pick path, admin or public: assert the team is
     * free, insert at the next pick number, translate a unique-violation race
     * into the same message the free-team check gives, and auto-complete the
     * draft if that was the last pick.
     *
     * Must be called inside the caller's transaction, which already holds the
     * draft row's lock — same contract every other method on this class has.
     *
     * @throws ValidationException
     */
    public static function recordPick(Draft $draft, DraftMember $member, DraftTeam $team, bool $madeByAdmin): DraftPick
    {
        if ($team->draftPick()->exists()) {
            throw ValidationException::withMessages([
                'draftTeamId' => 'That team was just taken.',
            ]);
        }

        $pick = new DraftPick([
            'draft_id' => $draft->id,
            'draft_member_id' => $member->id,
            'draft_team_id' => $team->id,
            'pick_number' => self::nextPickNumber($draft),
            'made_by_admin' => $madeByAdmin,
        ]);

        try {
            $pick->save();
        } catch (QueryException $e) {
            if ($e->getCode() === '23505') {
                throw ValidationException::withMessages([
                    'draftTeamId' => 'That team was just taken.',
                ]);
            }
            throw $e;
        }

        if (self::isComplete($draft)) {
            $draft->status = DraftStatus::COMPLETE;
            $draft->save();
        }

        return $pick;
    }

    public static function onTheClock(Draft $draft): ?DraftMember
    {
        if (self::isComplete($draft)) {
            return null;
        }

        $position = (self::pickCount($draft) % self::memberCount($draft)) + 1;

        return $draft->draftMembers()
            ->where('pick_position', '=', $position)
            ->first();
    }

    public static function nextPickNumber(Draft $draft): int
    {
        return self::pickCount($draft) + 1;
    }

    public static function currentRound(Draft $draft): int
    {
        $memberCount = self::memberCount($draft);

        if ($memberCount === 0) {
            return 1;
        }

        return intdiv(self::pickCount($draft), $memberCount) + 1;
    }

    public static function isComplete(Draft $draft): bool
    {
        return self::pickCount($draft) >= self::memberCount($draft) * $draft->total_rounds;
    }

    /**
     * The admin-panel guard for entering in_progress: memberCount() below
     * only counts ordered members, so an unordered roster would otherwise
     * read as a 0-member, already-complete draft. See admin-crud.md's
     * "Draft invariants" section for the full reasoning.
     */
    public static function rosterIsFullyOrdered(Draft $draft): bool
    {
        $memberCount = $draft->draftMembers()->count();
        if ($memberCount === 0) {
            return false;
        }

        $orderedCount = $draft->draftMembers()->whereNotNull('pick_position')->count();
        $maxPosition = $draft->draftMembers()->max('pick_position');

        return $orderedCount === $memberCount && $maxPosition === $memberCount;
    }

    private static function pickCount(Draft $draft): int
    {
        return $draft->draftPicks()->count();
    }

    /**
     * Members without a pick_position are excluded. The rotation is defined
     * over the ordered list, and counting an unordered member would advance the
     * clock to a position nobody holds, leaving the draft stuck with nobody
     * able to pick.
     */
    private static function memberCount(Draft $draft): int
    {
        return $draft->draftMembers()
            ->whereNotNull('pick_position')
            ->count();
    }
}
