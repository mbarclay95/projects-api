<?php

namespace App\Http\Controllers\Drafts;

use App\Http\Controllers\Drafts\Concerns\ScopesToDraftAdmins;
use App\Models\Drafts\Draft;
use App\Models\Drafts\DraftTeam;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Mbarclay36\LaravelCrud\CrudController;

class DraftController extends CrudController
{
    use ScopesToDraftAdmins;

    protected static string $modelClass = Draft::class;

    protected static array $indexRules = [
        'showArchived' => 'required|bool',
        'search' => 'nullable|string',
    ];

    protected static array $storeRules = [
        'name' => 'required|string',
        'notes' => 'nullable|string',
        'draftDate' => 'nullable|date',
        'totalRounds' => 'nullable|integer',
        'maxParticipants' => 'nullable|integer',
    ];

    protected static array $updateRules = [
        'name' => 'required|string',
        'notes' => 'nullable|string',
        'draftDate' => 'nullable|date',
        'totalRounds' => 'nullable|integer',
        'maxParticipants' => 'nullable|integer',
        'status' => 'nullable|string|in:signup,locked,in_progress,complete',
    ];

    /**
     * The CRUD package's default compares $model->user_id, a column drafts
     * does not have — see schema.md#for-user-hazard. Membership in
     * draft_admins is the actual authorization, for both overrides below.
     */
    public function cannotUpdate(Authenticatable $user, Model $model): bool
    {
        return !$user->hasPermissionTo(Draft::updateForUserPermission())
            || !$this->administers($user, $model->id);
    }

    public function cannotDestroy(Authenticatable $user, Model $model): bool
    {
        return !$user->hasPermissionTo(Draft::deleteForUserPermission())
            || !$this->administers($user, $model->id);
    }

    /**
     * Copies append rather than replace: new sort_order values continue from
     * the target's current maximum, so cloning into a draft that already has
     * teams does the obvious thing and cloning twice is visible.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function cloneTeams(Request $request, int $draftId): JsonResponse
    {
        $validated = $request->validate(['sourceDraftId' => 'required|integer']);
        $sourceDraftId = (int) $validated['sourceDraftId'];

        /** @var Authenticatable $user */
        $user = Auth::user();
        if (!$this->administers($user, $draftId) || !$this->administers($user, $sourceDraftId)) {
            throw new AuthenticationException();
        }

        if ($sourceDraftId === $draftId) {
            throw ValidationException::withMessages([
                'sourceDraftId' => 'A draft cannot be cloned from itself.',
            ]);
        }

        $teams = DB::transaction(function () use ($draftId, $sourceDraftId) {
            $nextSortOrder = (DraftTeam::query()->where('draft_id', '=', $draftId)->max('sort_order') ?? 0) + 1;

            $sourceTeams = DraftTeam::query()->where('draft_id', '=', $sourceDraftId)->orderBy('sort_order')->get();
            foreach ($sourceTeams as $sourceTeam) {
                $draftTeam = new DraftTeam([
                    'draft_id' => $draftId,
                    'name' => $sourceTeam->name,
                    's3_path' => $sourceTeam->s3_path,
                    'sort_order' => $nextSortOrder,
                ]);
                $draftTeam->save();
                $nextSortOrder++;
            }

            return DraftTeam::query()->where('draft_id', '=', $draftId)->orderBy('sort_order')->get();
        });

        return new JsonResponse(DraftTeam::toApiModels($teams));
    }
}
