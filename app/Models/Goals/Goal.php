<?php

namespace App\Models\Goals;

use App\Models\User;
use App\Repositories\GoalsRepository;
use App\Traits\HasApiModel;
use Carbon\Carbon;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Mbarclay36\LaravelCrud\ApiModel;
use Mbarclay36\LaravelCrud\Traits\HasRepository;

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
 * @property integer current_amount
 *
 * @property integer user_id
 * @property User user
 *
 * @property Collection|GoalDay[] goalDays
 */
class Goal extends ApiModel
{
    use HasFactory, HasRepository, Filterable;

    protected static string $repositoryClass = GoalsRepository::class;

    protected static array $apiModelAttributes = ['id', 'created_at', 'title', 'expected_amount', 'unit',
        'length_of_time', 'equality', 'verb', 'singular_unit', 'plural_unit', 'current_amount'];
    protected static array $apiModelEntities = [];
    protected static array $apiModelArrayEntities = [
        'goalDays' => GoalDay::class
    ];

    protected $appends = ['current_amount'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function goalDays(): HasMany
    {
        return $this->hasMany(GoalDay::class);
    }

    public function getSingularUnitAttribute(): string
    {
        return (Str::singular($this->unit));
    }

    public function getPluralUnitAttribute(): string
    {
        return (Str::plural($this->unit));
    }

    public function getCurrentAmount(Carbon $date): self
    {
        $this->current_amount = match ($this->length_of_time) {
            'week' => GoalDay::sumWeeklyAmount($date, $this->id),
            'month' => GoalDay::sumMonthlyAmount($date, $this->id)
        };

        return $this;
    }
}
