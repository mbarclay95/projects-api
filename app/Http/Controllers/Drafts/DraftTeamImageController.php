<?php

namespace App\Http\Controllers\Drafts;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Drafts\Concerns\ScopesToDraftAdmins;
use App\Models\Drafts\DraftTeam;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DraftTeamImageController extends Controller
{
    use ScopesToDraftAdmins;

    /**
     * Authorized with cannotUpdate() rather than cannotStore(): this modifies
     * an existing team, and cannotUpdate is the check that already carries
     * the draft_admins membership rule.
     *
     * @throws AuthenticationException
     */
    public function store(Request $request, int $draftTeamId): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:png,svg,jpg,jpeg,webp|max:2048',
        ]);

        /** @var DraftTeam $draftTeam */
        $draftTeam = DraftTeam::query()->findOrFail($draftTeamId);
        if ($this->cannotUpdate(Auth::user(), $draftTeam)) {
            throw new AuthenticationException();
        }

        $draftTeam->s3_path = Storage::disk('minio-s3')->put('draft-teams', $validated['file']);
        $draftTeam->save();

        return new JsonResponse(DraftTeam::toApiModel($draftTeam));
    }

    /**
     * Outside the auth middleware group on purpose — see admin-crud.md. An
     * <img src> never passes through AuthInterceptor, and this API is
     * IP-restricted at the nginx proxy regardless.
     */
    public function show(int $draftTeamId): StreamedResponse
    {
        /** @var DraftTeam $draftTeam */
        $draftTeam = DraftTeam::query()->find($draftTeamId);
        if (!$draftTeam || !$draftTeam->s3_path) {
            throw new NotFoundHttpException();
        }

        $file = Storage::disk('minio-s3')->get($draftTeam->s3_path);

        return response()->stream(function () use ($file) {
            echo $file;
        }, 200, ['Content-Type' => $this->contentType($draftTeam->s3_path)]);
    }

    private function cannotUpdate(Authenticatable $user, DraftTeam $draftTeam): bool
    {
        return !$user->hasPermissionTo(DraftTeam::updatePermission())
            || !$this->administers($user, $draftTeam->draft_id);
    }

    /**
     * Extension-based rather than SiteImageController's str_contains('.svg')
     * two-way branch, since five formats are accepted here instead of two.
     */
    private function contentType(string $path): string
    {
        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }
}
