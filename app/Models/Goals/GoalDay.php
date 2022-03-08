<?php

namespace App\Models\Goals;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class GoalDay
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property Carbon date
 * @property integer amount
 *
 * @property integer goal_id
 * @property Goal goal
 *
 * @property integer user_id
 * @property User user
 */
class GoalDay extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }
}
