<?php

namespace App\Http\Controllers\Drafts;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Drafts\Concerns\StreamsStoredImage;
use App\Models\Drafts\Draft;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * `GET public/drafts/{draftId}/image` — token-gated. Simpler than the team
 * equivalent, which has to resolve its token through a parent: the token is a
 * column on the row being asked for.
 *
 * No status gate, matching PublicDraftController::show(). Hiding the image
 * outside signup and locked is a display rule, applied on the public page.
 */
class PublicDraftImageController extends Controller
{
    use StreamsStoredImage;

    public function show(Request $request, int $draftId): StreamedResponse
    {
        $validated = $request->validate([
            'token' => 'required|string',
        ]);

        /** @var Draft|null $draft */
        $draft = Draft::query()->with('draftImage')->find($draftId);
        if (! $draft || $draft->token !== $validated['token']) {
            abort(404);
        }

        return $this->streamStoredImage($draft->draftImage?->s3_path);
    }
}
