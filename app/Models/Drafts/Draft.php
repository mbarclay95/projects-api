<?php

namespace App\Models\Drafts;

use App\Enums\DraftStatus;
use App\Models\BaseApiModel;
use App\Models\Users\User;
use App\Services\Drafts\DraftService;
use Carbon\Carbon;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Class Draft
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property string notes
 * @property Carbon draft_date
 * @property string token
 * @property DraftStatus status
 * @property integer total_rounds
 * @property integer max_participants
 *
 * @property integer created_by_id
 * @property User createdBy
 *
 * @property integer draft_image_id
 * @property DraftImage draftImage
 *
 * @property Collection|DraftAdmin[] draftAdmins
 * @property Collection|DraftTeam[] draftTeams
 * @property Collection|DraftMember[] draftMembers
 * @property Collection|DraftPick[] draftPicks
 */
class Draft extends BaseApiModel
{
    use HasFactory, SoftDeletes, Filterable;

    protected static array $apiModelAttributes = ['id', 'name', 'notes', 'draft_date', 'token', 'status',
        'total_rounds', 'max_participants', 'created_by_id', 'deleted_at'];

    protected static array $apiModelEntities = [
        'draftImage' => DraftImage::class,
    ];

    protected static array $apiModelArrayEntities = [
        'draftAdmins' => DraftAdmin::class,
        'draftTeams' => DraftTeam::class,
        'draftMembers' => DraftMember::class,
        'draftPicks' => DraftPick::class,
    ];

    protected $casts = [
        'draft_date' => 'datetime',
        'status' => DraftStatus::class,
    ];

    protected $dateFormat = 'Y-m-d H:i:sO';

    /**
     * Scoped by draft_admins membership rather than by a column on drafts,
     * because a draft has several admins and no single owner.
     */
    public static function getEntities($request, User $auth, bool $viewAnyForUser)
    {
        return Draft::query()
                    ->with(['draftImage', 'draftAdmins', 'draftTeams', 'draftMembers', 'draftPicks'])
                    ->whereHas('draftAdmins', fn ($query) => $query->where('user_id', '=', $auth->id))
                    ->orderBy('draft_date')
                    ->filter($request)
                    ->get();
    }

    /**
     * Scoped identically to getEntities() above, so polling this route
     * exposes nothing the list already didn't. 404 rather than null — the
     * generic show() would otherwise return a 200 with an empty body for a
     * draft this user does not administer.
     */
    public static function getEntity(int $entityId, User $auth, bool $viewForUser): Draft
    {
        $draft = Draft::query()
                      ->with(['draftImage', 'draftAdmins', 'draftTeams', 'draftMembers', 'draftPicks'])
                      ->whereHas('draftAdmins', fn ($query) => $query->where('user_id', '=', $auth->id))
                      ->find($entityId);

        if (!$draft) {
            abort(404);
        }

        return $draft;
    }

    /**
     * The creator's draft_admins row is written with the draft, in one
     * transaction, so a draft with no admin is never observable — the creator
     * would otherwise lose access to the thing they just made.
     */
    public static function createEntity($request, User $auth): Draft
    {
        $totalRounds = $request['totalRounds'] ?? 1;
        self::assertValidTotalRounds($totalRounds);

        return DB::transaction(function () use ($request, $auth, $totalRounds) {
            $draft = new Draft([
                'name' => $request['name'],
                'notes' => $request['notes'] ?? null,
                'draft_date' => $request['draftDate'] ?? null,
                'total_rounds' => $totalRounds,
                'max_participants' => $request['maxParticipants'] ?? null,
                'draft_image_id' => $request['draftImageId'] ?? null,
                'status' => DraftStatus::SIGNUP,
                'token' => Str::random(),
            ]);
            $draft->createdBy()->associate($auth);
            $draft->save();
            $draft->draftAdmins()->create(['user_id' => $auth->id]);

            return $draft;
        });
    }

    /**
     * @param Draft $entity
     * @param $request
     * @param User $auth
     * @return Draft
     * @throws ValidationException
     */
    public static function updateEntity(Model $entity, $request, User $auth): Model
    {
        $totalRounds = $request['totalRounds'] ?? $entity->total_rounds;
        self::assertValidTotalRounds($totalRounds);
        if ($totalRounds < DraftService::currentRound($entity)) {
            throw ValidationException::withMessages([
                'totalRounds' => 'Total rounds cannot drop below the round already in progress.',
            ]);
        }

        $maxParticipants = $request['maxParticipants'] ?? null;
        if ($maxParticipants !== null && $maxParticipants < $entity->draftMembers()->count()) {
            throw ValidationException::withMessages([
                'maxParticipants' => 'The participant cap cannot be set below the current member count.',
            ]);
        }

        $status = $request['status'] ?? $entity->status->value;
        if ($status === DraftStatus::IN_PROGRESS->value && !DraftService::rosterIsFullyOrdered($entity)) {
            throw ValidationException::withMessages([
                'status' => 'Every member needs a pick position, 1 through N with no gaps, before the draft can start.',
            ]);
        }

        $entity->name = $request['name'];
        $entity->notes = $request['notes'] ?? null;
        $entity->draft_date = $request['draftDate'] ?? null;
        $entity->total_rounds = $totalRounds;
        $entity->max_participants = $maxParticipants;
        $entity->draft_image_id = $request['draftImageId'] ?? null;
        $entity->status = $status;
        $entity->save();

        return $entity;
    }

    /**
     * @throws ValidationException
     */
    private static function assertValidTotalRounds(int $totalRounds): void
    {
        if ($totalRounds < 1) {
            throw ValidationException::withMessages(['totalRounds' => 'A draft must have at least one round.']);
        }
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function draftImage(): BelongsTo
    {
        return $this->belongsTo(DraftImage::class);
    }

    public function draftAdmins(): HasMany
    {
        return $this->hasMany(DraftAdmin::class);
    }

    public function draftTeams(): HasMany
    {
        return $this->hasMany(DraftTeam::class);
    }

    public function draftMembers(): HasMany
    {
        return $this->hasMany(DraftMember::class);
    }

    public function draftPicks(): HasMany
    {
        return $this->hasMany(DraftPick::class);
    }
}
