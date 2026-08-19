<?php

namespace App\Models\Drafts;

use App\Models\BaseApiModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class DraftPick
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property int pick_number
 * @property bool made_by_admin
 * @property int draft_id
 * @property Draft draft
 * @property int draft_member_id
 * @property DraftMember draftMember
 * @property int draft_team_id
 * @property DraftTeam draftTeam
 */
class DraftPick extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'draft_id', 'draft_member_id', 'draft_team_id',
        'pick_number', 'made_by_admin'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    protected $casts = [
        'made_by_admin' => 'boolean',
    ];

    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }

    public function draftMember(): BelongsTo
    {
        return $this->belongsTo(DraftMember::class);
    }

    public function draftTeam(): BelongsTo
    {
        return $this->belongsTo(DraftTeam::class);
    }
}
