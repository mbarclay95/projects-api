<?php

namespace App\Models\Drafts;

use App\Models\BaseApiModel;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Class DraftMember
 *
 * A participant. Never has an account — identity is the secret below, held in
 * localStorage. Unrelated to DraftAdmin.
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property string secret
 * @property integer pick_position
 *
 * @property integer draft_id
 * @property Draft draft
 *
 * @property Collection|DraftPick[] draftPicks
 */
class DraftMember extends BaseApiModel
{
    use HasFactory;

    /**
     * `secret` is deliberately absent and must stay absent. The draft read
     * returns every member, so exposing it hands everyone else's credential to
     * anyone holding the link.
     */
    protected static array $apiModelAttributes = ['id', 'draft_id', 'name', 'pick_position'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    /**
     * `pick_position` is left null here — it is only ever set in bulk, by the
     * position endpoint, never at creation.
     */
    public static function createEntity($request, User $auth): DraftMember
    {
        $draftMember = new DraftMember([
            'draft_id' => $request['draftId'],
            'name' => $request['name'],
            'secret' => Str::random(32),
        ]);
        $draftMember->save();

        return $draftMember;
    }

    /**
     * @param DraftMember $entity
     * @param $request
     * @param User $auth
     * @return DraftMember|Model
     */
    public static function updateEntity(Model $entity, $request, User $auth): Model
    {
        $entity->name = $request['name'];
        $entity->save();

        return $entity;
    }

    /**
     * @throws ValidationException
     */
    public static function destroyEntity(Model $entity, User $auth): void
    {
        if ($entity->draftPicks()->exists()) {
            throw ValidationException::withMessages([
                'member' => 'A member that has picks cannot be deleted.',
            ]);
        }

        $entity->delete();
    }

    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }

    public function draftPicks(): HasMany
    {
        return $this->hasMany(DraftPick::class);
    }
}
