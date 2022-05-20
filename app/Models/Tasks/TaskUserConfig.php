<?php

namespace App\Models\Tasks;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Family
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property integer tasks_per_week
 *
 * @property integer family_id
 * @property Family family
 *
 * @property integer user_id
 * @property User user
 */
class TaskUserConfig extends Model
{
    use HasFactory;

    protected static $unguarded = true;

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
