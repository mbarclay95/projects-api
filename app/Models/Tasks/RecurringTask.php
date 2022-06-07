<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

/**
 * Class RecurringTask
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property string description
 * @property integer frequency_amount
 * @property string frequency_unit
 *
 * @property string owner_type
 * @property integer owner_id
 * @property User|Family owner
 *
 * @property Collection|Tag[] tags
 */
class RecurringTask extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'description', 'frequency_amount', 'frequency_unit',
        'owner_type', 'owner_id'];

    protected static array $apiModelEntities = [];

    protected static array $apiModelArrayEntities = [];

    public static function createEntity($request, User $auth): RecurringTask
    {
        $task = new RecurringTask([
            'name' => $request['name'],
            'description' => $request['description'],
            'owner_type' => $request['ownerType'] === 'family' ? Family::class : User::class,
            'owner_id' => $request['ownerId'],
            'frequency_amount' => $request['frequencyAmount'],
            'frequency_unit' => $request['frequencyUnit'],
        ]);
        $task->save();

        $dueDate = Carbon::parse($request['dueDate'])->setTimezone('America/Los_Angeles')->startOfDay();
        $task->createFutureTasks($dueDate);

        return $task;
    }

    public function createFutureTasks(Carbon $startDate, int $numOfTasks = 2): void
    {
        $futureTasks = Task::getFutureIncompleteTasks($this->id);

        if (count($futureTasks) >= $numOfTasks) {
            return;
        }

        for ($i = 0 ; $i < $numOfTasks ; $i++) {
            $foundTask = $futureTasks->where('due_date', '=', $startDate)->first();
            if ($foundTask) {
                continue;
            }
            Task::createFromRecurring($this, $startDate);
            $startDate = $this->incrementDateByFrequency($startDate);
        }
    }

    public function incrementDateByFrequency(Carbon $date): Carbon
    {
        if ($this->frequency_unit == 'day') {
            return $date->addDays($this->frequency_amount);
        }
        if ($this->frequency_unit == 'week') {
            return $date->addWeeks($this->frequency_amount);
        }

        return $date->addMonths($this->frequency_amount);
    }

    public function owner(): MorphTo
    {
        return $this->morphTo('owner', 'owner_type', 'owner_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
