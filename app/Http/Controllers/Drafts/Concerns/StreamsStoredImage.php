<?php

namespace App\Http\Controllers\Drafts\Concerns;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The minio stream and the five-format content-type match, shared by the four
 * draft image routes — the admin and public streams for both team logos
 * (`DraftTeamImageController`, `PublicDraftTeamImageController`) and draft
 * images (`DraftImageController`, `PublicDraftImageController`) — so this
 * logic lives exactly once. See public-draft.md.
 *
 * Takes the path rather than a model because the two features store it
 * differently: a team logo is a column on `draft_teams`, a draft image is a
 * row in `draft_images` the draft points at.
 */
trait StreamsStoredImage
{
    protected function streamStoredImage(?string $s3Path): StreamedResponse
    {
        if (!$s3Path) {
            throw new NotFoundHttpException();
        }

        $file = Storage::disk('minio-s3')->get($s3Path);

        return response()->stream(function () use ($file) {
            echo $file;
        }, 200, ['Content-Type' => $this->storedImageContentType($s3Path)]);
    }

    /**
     * Extension-based rather than a two-way `str_contains('.svg')` branch,
     * since five formats are accepted here instead of two.
     */
    private function storedImageContentType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }
}
