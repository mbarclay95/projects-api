<?php

namespace App\Models\Drafts;

use App\Models\BaseApiModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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

    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }

    public function draftPicks(): HasMany
    {
        return $this->hasMany(DraftPick::class);
    }
}
