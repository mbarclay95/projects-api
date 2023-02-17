<?php

namespace App\Models\Goals;

use App\Models\User;
use App\Repositories\GoalDaysRepository;
use Carbon\Carbon;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Mbarclay36\LaravelCrud\ApiModel;
use Mbarclay36\LaravelCrud\Traits\HasRepository;

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
class GoalDay extends ApiModel
{
    use HasFactory, Filterable, HasRepository;

    protected static string $repository = GoalDaysRepository::class;

    protected static array $apiModelAttributes = ['id', 'date', 'amount'];
    protected static array $apiModelEntities = [];
    protected static array $apiModelArrayEntities = [];

    protected $dateFormat = 'Y-m-d H:i:sO';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    public function getDateAttribute($value): bool|Carbon
    {
        return Carbon::createFromFormat('Y-m-d', $value, 'America/Los_Angeles')->startOfDay();
    }
}
