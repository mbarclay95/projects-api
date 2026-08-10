<?php

namespace App\Http\Controllers\Drafts;

use App\Http\Controllers\Controller;
use App\Models\ApiModels\PublicDraftApiModel;
use App\Models\Drafts\Draft;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * The `public/` route group — no auth, reachable only from
 * projects-api-public. Every route here checks `drafts.token` against the
 * row it is about to touch and aborts 404 on mismatch itself; the proxy
 * checks too, but that is not what makes these safe. See public-draft.md.
 */
class PublicDraftController extends Controller
{
    /**
     * No status gate — a draft is readable in every status. During `signup`
     * that means the roster of names is visible to anyone with the link,
     * which is the point.
     */
    public function show(Request $request, int $draftId): JsonResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        /** @var Draft|null $draft */
        $draft = Draft::query()
                      ->with(['draftTeams', 'draftMembers', 'draftPicks'])
                      ->find($draftId);
        if (!$draft || $draft->token !== $validated['token']) {
            abort(404);
        }

        return new JsonResponse(PublicDraftApiModel::toApiModel($draft));
    }
}
