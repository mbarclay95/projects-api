<?php

namespace App\Http\Controllers\Drafts;

use App\Enums\DraftStatus;
use App\Http\Controllers\Controller;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftPick;
use App\Services\Drafts\DraftService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * `POST public/draft-picks` — make a pick. One transaction, opened with
 * `lockForUpdate()` on the draft row, per schema.md#concurrency: locking the
 * row serialises every pick for that draft and makes the whole
 * check-and-insert atomic. The unique(draft_id, draft_team_id) index stays as
 * the backstop, caught below rather than left to 500.
 */
class PublicDraftPickController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'draftId' => 'required|integer',
            'token' => 'required|string',
            'secret' => 'required|string',
            'draftTeamId' => 'required|integer',
        ]);

        $result = DB::transaction(function () use ($validated) {
            /** @var Draft|null $draft */
            $draft = Draft::query()->lockForUpdate()->find($validated['draftId']);
            if (!$draft || $draft->token !== $validated['token']) {
                abort(404);
            }

            // Scoped to this draft, so a secret from another draft never
            // matches — one member's credential can't pick anywhere else.
            $member = $draft->draftMembers()->where('secret', '=', $validated['secret'])->first();
            if (!$member) {
                abort(404);
            }

            if ($draft->status !== DraftStatus::IN_PROGRESS) {
                throw ValidationException::withMessages([
                    'draftId' => 'This draft is not in progress.',
                ]);
            }

            $onTheClock = DraftService::onTheClock($draft);
            if (!$onTheClock || $onTheClock->id !== $member->id) {
                throw ValidationException::withMessages([
                    'draftTeamId' => "It's not your turn.",
                ]);
            }

            $team = $draft->draftTeams()->find($validated['draftTeamId']);
            if (!$team) {
                throw ValidationException::withMessages([
                    'draftTeamId' => 'That team is not part of this draft.',
                ]);
            }

            if ($team->draftPick()->exists()) {
                throw ValidationException::withMessages([
                    'draftTeamId' => 'That team was just taken.',
                ]);
            }

            $pick = new DraftPick([
                'draft_id' => $draft->id,
                'draft_member_id' => $member->id,
                'draft_team_id' => $team->id,
                'pick_number' => DraftService::nextPickNumber($draft),
                'made_by_admin' => false,
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

            if (DraftService::isComplete($draft)) {
                $draft->status = DraftStatus::COMPLETE;
                $draft->save();
            }

            return [
                'pick' => $pick,
                'draft' => $draft,
                'onTheClock' => DraftService::onTheClock($draft),
            ];
        });

        return new JsonResponse([
            'pick' => [
                'id' => $result['pick']->id,
                'pickNumber' => $result['pick']->pick_number,
                'draftMemberId' => $result['pick']->draft_member_id,
                'draftTeamId' => $result['pick']->draft_team_id,
                'madeByAdmin' => $result['pick']->made_by_admin,
            ],
            'status' => $result['draft']->status->value,
            'currentPickNumber' => DraftService::nextPickNumber($result['draft']),
            'onTheClockMemberId' => $result['onTheClock']?->id,
        ]);
    }
}
