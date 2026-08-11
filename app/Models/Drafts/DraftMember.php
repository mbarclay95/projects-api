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
 * @property Carbon|null claimed_at
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
    protected static array $apiModelAttributes = ['id', 'draft_id', 'name', 'pick_position', 'claimed_at'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    protected $casts = [
        'claimed_at' => 'datetime',
    ];

    /**
     * `pick_position` is left null here — it is only ever set in bulk, by the
     * position endpoint, never at creation.
     *
     * @throws ValidationException
     */
    public static function createEntity($request, User $auth): DraftMember
    {
        static::assertNameNotTaken((int) $request['draftId'], $request['name']);

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
     * @throws ValidationException
     */
    public static function updateEntity(Model $entity, $request, User $auth): Model
    {
        static::assertNameNotTaken($entity->draft_id, $request['name'], $entity->id);

        $entity->name = $request['name'];
        $entity->save();

        return $entity;
    }

    /**
     * Same rule and the same comparison as `PublicDraftMemberController::store()`
     * — trimmed and case-insensitive, per public-draft.md — so a name taken
     * from one side is taken from the other. No `lockForUpdate()` here: unlike
     * the public claim flow, which serialises many participants racing over a
     * 3s poll, this runs under one admin's authenticated request at a time.
     *
     * @throws ValidationException
     */
    private static function assertNameNotTaken(int $draftId, string $name, ?int $excludingId = null): void
    {
        $nameTaken = static::query()
            ->where('draft_id', '=', $draftId)
            ->when($excludingId, fn ($query) => $query->where('id', '!=', $excludingId))
            ->whereRaw('trim(lower(name)) = ?', [trim(strtolower($name))])
            ->exists();

        if ($nameTaken) {
            throw ValidationException::withMessages([
                'name' => 'That name is already taken in this draft.',
            ]);
        }
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
