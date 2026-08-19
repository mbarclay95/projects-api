<?php

namespace App\Http\Controllers\Drafts;

use App\Enums\DraftStatus;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Drafts\Concerns\ScopesToDraftAdmins;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftPick;
use App\Services\Drafts\DraftService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * The admin's in-draft controls: pick for whoever is on the clock, correct a
 * mis-click, undo. A plain controller rather than a CrudController subclass —
 * all three verbs need the same locked transaction and return the same board
 * delta, which is not what CrudController returns for any of them.
 */
class DraftPickController extends Controller
{
    use ScopesToDraftAdmins;

    /**
     * No draftMemberId — the pick always goes to whoever is on the clock. See
     * in-draft-control.md's decision 5 for why: attributing a pick to a
     * different member than the one it consumes the slot of corrupts the
     * rotation silently.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'draftId' => 'required|integer',
            'draftTeamId' => 'required|integer',
        ]);

        if (! Auth::user()->hasPermissionTo(DraftPick::createPermission())
            || ! $this->administers(Auth::user(), $validated['draftId'])) {
            throw new AuthenticationException;
        }

        $result = DB::transaction(function () use ($validated) {
            /** @var Draft|null $draft */
            $draft = Draft::query()->lockForUpdate()->find($validated['draftId']);
            if (! $draft) {
                abort(404);
            }

            if ($draft->status !== DraftStatus::IN_PROGRESS) {
                throw ValidationException::withMessages([
                    'draftId' => 'This draft is not in progress.',
                ]);
            }

            $onTheClock = DraftService::onTheClock($draft);
            if (! $onTheClock) {
                throw ValidationException::withMessages([
                    'draftId' => 'Nobody is on the clock.',
                ]);
            }

            $team = $draft->draftTeams()->find($validated['draftTeamId']);
            if (! $team) {
                throw ValidationException::withMessages([
                    'draftTeamId' => 'That team is not part of this draft.',
                ]);
            }

            $pick = DraftService::recordPick($draft, $onTheClock, $team, true);

            return ['pick' => $pick, 'draft' => $draft];
        });

        return $this->envelope($result['pick'], $result['draft']);
    }

    /**
     * Body is `{ draftTeamId }` only. `pick_number` and `draft_member_id`
     * cannot be touched through this route — a gap in pick_number silently
     * corrupts the clock for the rest of the draft. `made_by_admin` is left
     * alone too: it records who created the pick, not who last corrected it.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'draftTeamId' => 'required|integer',
        ]);

        /** @var DraftPick $pick */
        $pick = DraftPick::query()->findOrFail($id);

        if (! Auth::user()->hasPermissionTo(DraftPick::updatePermission())
            || ! $this->administers(Auth::user(), $pick->draft_id)) {
            throw new AuthenticationException;
        }

        $result = DB::transaction(function () use ($validated, $pick) {
            /** @var Draft $draft */
            $draft = Draft::query()->lockForUpdate()->find($pick->draft_id);

            $team = $draft->draftTeams()->find($validated['draftTeamId']);
            if (! $team) {
                throw ValidationException::withMessages([
                    'draftTeamId' => 'That team is not part of this draft.',
                ]);
            }

            $existingPick = $team->draftPick()->first();
            if ($existingPick && $existingPick->id !== $pick->id) {
                throw ValidationException::withMessages([
                    'draftTeamId' => 'That team is already picked.',
                ]);
            }

            $pick->draft_team_id = $team->id;
            $pick->save();

            return ['pick' => $pick, 'draft' => $draft];
        });

        return $this->envelope($result['pick'], $result['draft']);
    }

    /**
     * Only the highest pick_number for the draft can be undone — repeat to
     * walk back further. Reverses auto-complete per decision 6: if that pick
     * had completed the draft, undoing it reopens play.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var DraftPick $pick */
        $pick = DraftPick::query()->findOrFail($id);

        if (! Auth::user()->hasPermissionTo(DraftPick::deletePermission())
            || ! $this->administers(Auth::user(), $pick->draft_id)) {
            throw new AuthenticationException;
        }

        $result = DB::transaction(function () use ($pick) {
            /** @var Draft $draft */
            $draft = Draft::query()->lockForUpdate()->find($pick->draft_id);

            $maxPickNumber = $draft->draftPicks()->max('pick_number');
            if ($pick->pick_number !== $maxPickNumber) {
                throw ValidationException::withMessages([
                    'id' => 'Only the most recent pick can be undone.',
                ]);
            }

            $wasComplete = $draft->status === DraftStatus::COMPLETE;

            $pick->delete();

            if ($wasComplete && ! DraftService::isComplete($draft)) {
                $draft->status = DraftStatus::IN_PROGRESS;
                $draft->save();
            }

            return ['draft' => $draft];
        });

        return $this->envelope(null, $result['draft']);
    }

    private function envelope(?DraftPick $pick, Draft $draft): JsonResponse
    {
        return new JsonResponse([
            'pick' => $pick ? DraftPick::toApiModel($pick) : null,
            'status' => $draft->status->value,
        ]);
    }
}
