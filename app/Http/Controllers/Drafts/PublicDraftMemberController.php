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
 * `POST public/draft-members` — claim a name. This is the only way a member
 * is created from the public side; there is deliberately no "claim an
 * admin-created member" flow (see public-draft.md's "new names only"
 * decision).
 */
class PublicDraftMemberController extends Controller
{
    /**
     * Everything past the token check runs under the draft row's lock. There
     * is no unique(draft_id, name) index, so the lock is the only thing
     * closing the duplicate-name race and the two-people-take-the-last-spot
     * race — see public-draft.md.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'draftId' => 'required|integer',
            'token' => 'required|string',
            'name' => 'required|string',
        ]);

        $member = DB::transaction(function () use ($validated) {
            /** @var Draft|null $draft */
            $draft = Draft::query()->lockForUpdate()->find($validated['draftId']);
            if (!$draft || $draft->token !== $validated['token']) {
                abort(404);
            }

            if ($draft->status !== DraftStatus::SIGNUP) {
                throw ValidationException::withMessages([
                    'draftId' => 'This draft is not open for signup.',
                ]);
            }

            $name = $validated['name'];
            $nameTaken = $draft->draftMembers()
                               ->whereRaw('trim(lower(name)) = ?', [trim(strtolower($name))])
                               ->exists();
            if ($nameTaken) {
                throw ValidationException::withMessages([
                    'name' => 'That name is already taken in this draft.',
                ]);
            }

            if ($draft->max_participants !== null
                && $draft->draftMembers()->count() >= $draft->max_participants) {
                throw ValidationException::withMessages([
                    'draftId' => 'This draft is full.',
                ]);
            }

            $member = new DraftMember([
                'draft_id' => $draft->id,
                'name' => $name,
                'secret' => Str::random(32),
                'claimed_at' => now(),
            ]);
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
