<?php

namespace App\Http\Controllers\Drafts;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Drafts\Concerns\StreamsStoredImage;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftImage;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * A draft image is uploaded on its own and attached to a draft by id when the
 * draft is saved, following `site-images` rather than `draft-team-images` —
 * that is what lets the organizer pick an image while creating a draft, before
 * there is a row to hang it on. The cost is that cancelling a create leaves an
 * unreferenced row here, which is accepted.
 */
class DraftImageController extends Controller
{
    use StreamsStoredImage;

    /**
     * Authorized against Draft::createPermission() rather than a permission of
     * its own: there is no draft to scope to yet, and every organizer holds
     * that grant through Roles::DRAFTS_ROLE, so this needs no seeder change.
     *
     * @throws AuthenticationException
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:png,svg,jpg,jpeg,webp|max:2048',
        ]);

        $user = Auth::user();
        if (!$user->hasPermissionTo(Draft::createPermission())) {
            throw new AuthenticationException();
        }

        $draftImage = new DraftImage([
            's3_path' => Storage::disk('minio-s3')->put('draft-images', $validated['file']),
            'original_file_name' => $validated['file']->getClientOriginalName(),
        ]);
        $draftImage->createdBy()->associate($user);
        $draftImage->save();

        return new JsonResponse(DraftImage::toApiModel($draftImage));
    }

    /**
     * Outside the auth middleware group on purpose — see admin-crud.md. An
     * <img src> never passes through AuthInterceptor, and this API is
     * IP-restricted at the nginx proxy regardless.
     */
    public function show(int $draftImageId): StreamedResponse
    {
        /** @var DraftImage|null $draftImage */
        $draftImage = DraftImage::query()->find($draftImageId);

        return $this->streamStoredImage($draftImage?->s3_path);
    }
}
