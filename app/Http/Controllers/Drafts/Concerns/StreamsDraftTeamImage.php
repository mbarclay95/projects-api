<?php

namespace App\Http\Controllers\Drafts\Concerns;

use App\Models\Drafts\DraftTeam;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * The minio stream and the five-format content-type match, shared by the
 * admin route (`DraftTeamImageController`) and the public one
 * (`PublicDraftTeamImageController`) so this logic lives exactly once. See
 * public-draft.md.
 */
trait StreamsDraftTeamImage
{
    protected function streamDraftTeamImage(?DraftTeam $draftTeam): StreamedResponse
    {
        if (!$draftTeam || !$draftTeam->s3_path) {
            throw new NotFoundHttpException();
        }

        $file = Storage::disk('minio-s3')->get($draftTeam->s3_path);

        return response()->stream(function () use ($file) {
            echo $file;
        }, 200, ['Content-Type' => $this->draftTeamImageContentType($draftTeam->s3_path)]);
    }

    /**
     * Extension-based rather than a two-way `str_contains('.svg')` branch,
     * since five formats are accepted here instead of two.
     */
    private function draftTeamImageContentType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }
}
