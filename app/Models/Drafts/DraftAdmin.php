<?php

namespace App\Models\Drafts;

use App\Models\BaseApiModel;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function draft(): BelongsTo
    {
        return $this->belongsTo(Draft::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
