<?php

namespace App\Models\Drafts;

use App\Models\BaseApiModel;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

/**
 * Class DraftAdmin
 *
 * A user who can administer a draft. Unrelated to DraftMember, which is a
 * participant with no account at all.
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property integer draft_id
 * @property Draft draft
 *
 * @property integer user_id
 * @property User user
 */
class DraftAdmin extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'draft_id', 'user_id'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    /**
     * No updateEntity: the table is (draft_id, user_id) with no other column,
     * so there is nothing an update could change.
     *
     * @throws ValidationException
     */
    public static function createEntity($request, User $auth): DraftAdmin
    {
        $alreadyAdmin = static::query()
                              ->where('draft_id', '=', $request['draftId'])
                              ->where('user_id', '=', $request['userId'])
                              ->exists();
        if ($alreadyAdmin) {
            throw ValidationException::withMessages([
                'userId' => 'This user is already an admin on this draft.',
            ]);
        }

        $draftAdmin = new DraftAdmin([
            'draft_id' => $request['draftId'],
            'user_id' => $request['userId'],
        ]);
        $draftAdmin->save();

        return $draftAdmin;
    }

    /**
     * The creator's row can never be removed, which is what makes "a draft
     * always has at least one admin" true by construction.
     *
     * @throws ValidationException
     */
    public static function destroyEntity(Model $entity, User $auth): void
    {
        if ($entity->user_id === $entity->draft->created_by_id) {
            throw ValidationException::withMessages([
                'draftAdmin' => 'The draft creator cannot be removed as an admin.',
            ]);
        }

        $entity->delete();
    }

    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
