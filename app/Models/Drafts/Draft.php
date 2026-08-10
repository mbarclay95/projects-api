<?php

namespace App\Models\Drafts;

use App\Enums\DraftStatus;
use App\Models\BaseApiModel;
use App\Models\Users\User;
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

    protected static array $apiModelEntities = [];

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
                    ->with(['draftAdmins', 'draftTeams', 'draftMembers', 'draftPicks'])
                    ->whereHas('draftAdmins', fn ($query) => $query->where('user_id', '=', $auth->id))
                    ->orderBy('draft_date')
                    ->filter($request)
                    ->get();
    }

    /**
     * The creator's draft_admins row is written with the draft, in one
     * transaction, so a draft with no admin is never observable — the creator
     * would otherwise lose access to the thing they just made.
     */
    public static function createEntity($request, User $auth): Draft
    {
        return DB::transaction(function () use ($request, $auth) {
            $draft = new Draft([
                'name' => $request['name'],
                'notes' => $request['notes'] ?? null,
                'draft_date' => $request['draftDate'] ?? null,
                'total_rounds' => $request['totalRounds'] ?? 1,
                'max_participants' => $request['maxParticipants'] ?? null,
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
     */
    public static function updateEntity(Model $entity, $request, User $auth): Model
    {
        $entity->name = $request['name'];
        $entity->notes = $request['notes'] ?? null;
        $entity->draft_date = $request['draftDate'] ?? null;
        $entity->total_rounds = $request['totalRounds'] ?? 1;
        $entity->max_participants = $request['maxParticipants'] ?? null;
        $entity->status = $request['status'] ?? $entity->status;
        $entity->save();

        return $entity;
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
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
