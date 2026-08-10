<?php

namespace App\Services\Drafts;

use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;

/**
 * Whose turn it is, derived rather than stored.
 *
 * Every method counts from the database rather than from loaded relations, so
 * the public pick endpoint can call these inside its lockForUpdate transaction
 * and see the picks another request just inserted.
 */
class DraftService
{
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
