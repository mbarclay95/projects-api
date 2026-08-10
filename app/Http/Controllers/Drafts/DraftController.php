<?php

namespace App\Http\Controllers\Drafts;

use App\Http\Controllers\Drafts\Concerns\ScopesToDraftAdmins;
use App\Models\Drafts\Draft;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
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
}
