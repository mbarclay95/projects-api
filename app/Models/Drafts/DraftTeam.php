<?php

namespace App\Models\Drafts;

use App\Models\BaseApiModel;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

/**
 * Class DraftTeam
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property string name
 * @property string s3_path
 * @property int sort_order
 * @property int draft_id
 * @property Draft draft
 * @property DraftPick draftPick
 */
class DraftTeam extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'draft_id', 'name', 's3_path', 'sort_order'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    public static function createEntity($request, User $auth): DraftTeam
    {
        $draftTeam = new DraftTeam([
            'draft_id' => $request['draftId'],
            'name' => $request['name'],
            'sort_order' => $request['sortOrder'] ?? 0,
        ]);
        $draftTeam->save();

        return $draftTeam;
    }

    /**
     * @param  DraftTeam  $entity
     * @return DraftTeam|Model
     */
    public static function updateEntity(Model $entity, $request, User $auth): Model
    {
        $entity->name = $request['name'];
        $entity->sort_order = $request['sortOrder'] ?? $entity->sort_order;
        $entity->save();

        return $entity;
    }

    /**
     * @throws ValidationException
     */
    public static function destroyEntity(Model $entity, User $auth): void
    {
        if ($entity->draftPick()->exists()) {
            throw ValidationException::withMessages([
                'team' => 'A team that has been picked cannot be deleted.',
            ]);
        }

        $entity->delete();
    }

    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }

    /**
     * HasOne rather than HasMany: unique(draft_id, draft_team_id) means a team
     * is taken at most once.
     */
    public function draftPick(): HasOne
    {
        return $this->hasOne(DraftPick::class);
    }
}
