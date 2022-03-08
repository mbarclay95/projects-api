<?php

namespace App\Models\Goals;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * Class Goal
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string title
 * @property string unit
 * @property string length_of_time
 * @property string equality
 * @property string verb
 * @property integer expected_amount
 *
 * @property integer user_id
 * @property User user
 *
 * @property Collection|GoalDay[] goalDays
 */
class Goal extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goalDays(): HasMany
    {
        return $this->hasMany(GoalDay::class);
    }
}
