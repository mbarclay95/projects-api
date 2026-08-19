<?php

namespace App\Http\Controllers\Drafts;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Drafts\Concerns\StreamsStoredImage;
use App\Models\Drafts\DraftTeam;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * `GET public/draft-teams/{draftTeamId}/image` — token-gated. `draft_teams`
 * carries no token of its own, so the token is checked against the team's
 * draft, resolved via `draft_teams.draft_id`.
 */
class PublicDraftTeamImageController extends Controller
{
    use StreamsStoredImage;

    public function show(Request $request, int $draftTeamId): StreamedResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        /** @var DraftTeam|null $draftTeam */
        $draftTeam = DraftTeam::query()->with('draft')->find($draftTeamId);
        if (! $draftTeam || $draftTeam->draft->token !== $validated['token']) {
            abort(404);
        }

        return $this->streamStoredImage($draftTeam->s3_path);
    }
}
