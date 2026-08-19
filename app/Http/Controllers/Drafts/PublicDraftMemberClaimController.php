<?php

namespace App\Http\Controllers\Drafts;

use App\Enums\DraftStatus;
use App\Http\Controllers\Controller;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftMember;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * `POST public/draft-member-claims` — bind this browser to an existing,
 * unclaimed member. The other half of "claim a name": `PublicDraftMemberController`
 * creates a member, this one binds to a member that already exists. See
 * in-draft-control.md's decision 1 for why the two don't share an endpoint.
 */
class PublicDraftMemberClaimController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'draftId' => 'required|integer',
            'token' => 'required|string',
            'draftMemberId' => 'required|integer',
        ]);

        $member = DB::transaction(function () use ($validated) {
            /** @var Draft|null $draft */
            $draft = Draft::query()->lockForUpdate()->find($validated['draftId']);
            if (! $draft || $draft->token !== $validated['token']) {
                abort(404);
            }

            /** @var DraftMember|null $member */
            $member = $draft->draftMembers()->find($validated['draftMemberId']);
            if (! $member) {
                abort(404);
            }

            if ($draft->status === DraftStatus::COMPLETE) {
                throw ValidationException::withMessages([
                    'draftMemberId' => 'This draft is finished.',
                ]);
            }

            if ($member->claimed_at !== null) {
                throw ValidationException::withMessages([
                    'draftMemberId' => 'That name has already been claimed.',
                ]);
            }

            $member->claimed_at = now();
            $member->secret = Str::random(32);
            $member->save();

            return $member;
        });

        return new JsonResponse([
            'id' => $member->id,
            'name' => $member->name,
            'secret' => $member->secret,
            'pickPosition' => $member->pick_position,
        ]);
    }
}
