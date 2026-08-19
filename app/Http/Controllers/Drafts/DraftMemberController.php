<?php

namespace App\Http\Controllers\Drafts;

use App\Enums\DraftStatus;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DraftMemberController extends DraftChildController
{
    protected static string $modelClass = DraftMember::class;

    protected static array $storeRules = [
        'draftId' => 'required|integer',
        'name' => 'required|string',
    ];

    protected static array $updateRules = [
        'name' => 'required|string',
    ];

    protected static array $updatePickPositionsRules = [
        'draftId' => 'required|integer',
        'positions' => 'required|array',
        'positions.*.draftMemberId' => 'required|integer',
        'positions.*.pickPosition' => 'required|integer',
    ];

    /**
     * Row-by-row updates would transiently violate unique(draft_id,
     * pick_position) whenever two members swap places, so every member is
     * cleared to null first — Postgres allows several nulls under a unique
     * index — and the real positions are assigned in a second pass.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function updatePickPositions(Request $request): JsonResponse
    {
        $validated = $request->validate(static::$updatePickPositionsRules);

        if (! $this->administers(Auth::user(), $validated['draftId'])) {
            throw new AuthenticationException;
        }

        $draft = Draft::query()->findOrFail($validated['draftId']);
        if (! in_array($draft->status, [DraftStatus::SIGNUP, DraftStatus::LOCKED], true)) {
            throw ValidationException::withMessages([
                'draftId' => 'The pick order can only be changed while the draft is in signup or locked.',
            ]);
        }

        $this->assertPositionsCoverEveryMember($draft, $validated['positions']);

        DB::transaction(function () use ($validated) {
            DraftMember::query()
                ->where('draft_id', '=', $validated['draftId'])
                ->update(['pick_position' => null]);

            foreach ($validated['positions'] as $position) {
                DraftMember::query()
                    ->where('draft_id', '=', $validated['draftId'])
                    ->where('id', '=', $position['draftMemberId'])
                    ->update(['pick_position' => $position['pickPosition']]);
            }
        });

        return new JsonResponse(['success' => true]);
    }

    /**
     * `DELETE draft-member-claims/{id}` — the admin half of claiming. Bespoke
     * rather than a stock CRUD verb, same precedent as
     * `updatePickPositions()`: the request never passes through
     * `CrudController::update()`, so the permission check has to happen here.
     *
     * No status gate — a correction has to work even on a `complete` draft.
     * Idempotent: clearing an already-unclaimed member still rotates the
     * secret and returns 200, since the UI only offers this on claimed rows
     * and the endpoint does not need to care.
     *
     * @throws AuthenticationException
     */
    public function clearClaim(int $draftMemberId): JsonResponse
    {
        $member = DraftMember::query()->findOrFail($draftMemberId);

        if (! Auth::user()->hasPermissionTo(DraftMember::updatePermission())
            || ! $this->administers(Auth::user(), $member->draft_id)) {
            throw new AuthenticationException;
        }

        $member->claimed_at = null;
        $member->secret = Str::random(32);
        $member->save();

        return new JsonResponse(DraftMember::toApiModel($member));
    }

    /**
     * @throws ValidationException
     */
    private function assertPositionsCoverEveryMember(Draft $draft, array $positions): void
    {
        $memberIds = $draft->draftMembers()->pluck('id')->sort()->values()->all();
        $submittedIds = collect($positions)->pluck('draftMemberId')->sort()->values()->all();
        if ($memberIds !== $submittedIds) {
            throw ValidationException::withMessages([
                'positions' => 'Every member of the draft must appear exactly once.',
            ]);
        }

        $pickPositions = collect($positions)->pluck('pickPosition')->sort()->values()->all();
        if ($pickPositions !== range(1, count($pickPositions))) {
            throw ValidationException::withMessages([
                'positions' => 'Positions must be 1 through N with no gaps.',
            ]);
        }
    }
}
