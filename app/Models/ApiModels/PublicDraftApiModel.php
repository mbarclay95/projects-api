<?php

namespace App\Models\ApiModels;

use App\Enums\DraftStatus;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use App\Models\Drafts\DraftPick;
use App\Models\Drafts\DraftTeam;
use App\Services\Drafts\DraftService;
use Illuminate\Support\Collection;

/**
 * The public draft payload — built by hand rather than through
 * `Draft::toApiModel()`, which exposes `token`, `DraftTeam.s3_path`, and
 * `draftAdmins`. None of those may reach a participant, and the derived
 * fields here (the clock, spots remaining) are not columns to begin with, so
 * the generic $apiModelAttributes plumbing does not fit. See public-draft.md.
 */
class PublicDraftApiModel
{
    public static function toApiModel(Draft $draft): array
    {
        $onTheClockMember = $draft->status === DraftStatus::IN_PROGRESS
            ? DraftService::onTheClock($draft)
            : null;

        return [
            'id' => $draft->id,
            'name' => $draft->name,
            'notes' => $draft->notes,
            'draftDate' => $draft->draft_date,
            'status' => $draft->status->value,
            'totalRounds' => $draft->total_rounds,
            'maxParticipants' => $draft->max_participants,
            'hasImage' => $draft->draft_image_id !== null,
            'spotsRemaining' => self::spotsRemaining($draft),
            'currentRound' => DraftService::currentRound($draft),
            'currentPickNumber' => DraftService::nextPickNumber($draft),
            'onTheClockMemberId' => $onTheClockMember?->id,
            'draftTeams' => self::teams($draft->draftTeams),
            'draftMembers' => self::members($draft->draftMembers),
            'draftPicks' => self::picks($draft->draftPicks),
        ];
    }

    private static function spotsRemaining(Draft $draft): ?int
    {
        if ($draft->max_participants === null) {
            return null;
        }

        return $draft->max_participants - $draft->draftMembers->count();
    }

    private static function teams(Collection $teams): array
    {
        return $teams->map(fn (DraftTeam $team) => [
            'id' => $team->id,
            'name' => $team->name,
            'sortOrder' => $team->sort_order,
            'hasImage' => $team->s3_path !== null,
        ])->all();
    }

    private static function members(Collection $members): array
    {
        return $members->map(fn (DraftMember $member) => [
            'id' => $member->id,
            'name' => $member->name,
            'pickPosition' => $member->pick_position,
            'claimed' => $member->claimed_at !== null,
        ])->all();
    }

    private static function picks(Collection $picks): array
    {
        return $picks->map(fn (DraftPick $pick) => [
            'id' => $pick->id,
            'pickNumber' => $pick->pick_number,
            'draftMemberId' => $pick->draft_member_id,
            'draftTeamId' => $pick->draft_team_id,
            'madeByAdmin' => $pick->made_by_admin,
        ])->all();
    }
}
