<?php

namespace App\Models\Drafts;

use App\Models\BaseApiModel;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class DraftTeam
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property string s3_path
 * @property integer sort_order
 *
 * @property integer draft_id
 * @property Draft draft
 *
 * @property DraftPick draftPick
 */
class DraftTeam extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'draft_id', 'name', 's3_path', 'sort_order'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    /**
     * There is no reorder endpoint for teams, only the append behaviour clone
     * shares below — so sort_order is always the next value, never client-set.
     */
    public static function createEntity($request, User $auth): DraftTeam
    {
        $draftTeam = new DraftTeam([
            'draft_id' => $request['draftId'],
            'name' => $request['name'],
            'sort_order' => static::query()->where('draft_id', '=', $request['draftId'])->max('sort_order') + 1,
        ]);
        $draftTeam->save();

        return $draftTeam;
    }

    /**
     * @param DraftTeam $entity
     * @param $request
     * @param User $auth
     * @return DraftTeam|Model
     */
    public static function updateEntity(Model $entity, $request, User $auth): Model
    {
        $entity->name = $request['name'];
        $entity->save();

        return $entity;
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
