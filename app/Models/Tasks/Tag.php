<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

/**
 * Class Tag
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string tag
 *
 * @property Collection|Task[] tasks
 * @property Collection|RecurringTask[] recurringTasks
 */
class Tag extends BaseApiModel
{
    use HasFactory;

    /**
     * @param Tag|null $model
     * @param array $hideItem
     * @return array|string|null
     */
    public static function toApiModel(?Model $model, array $hideItem = []): array|null|string
    {
        return $model->tag;
    }

    public static function getEntities($request, User $auth, bool $viewAnyForUser)
    {
        return Tag::query()
            ->whereHas('tasks', function ($where) use ($auth) {
                $where->where(function ($innerWhere) use ($auth) {
                    $innerWhere
                        ->orWhere(function ($userWhere) use ($auth) {
                            $userWhere->where('owner_type', '=', User::class)
                                      ->where('owner_id', '=', $auth->id);
                        })
                        ->when($auth->family, function ($familyCondition) use ($auth) {
                            $familyCondition->orWhere(function ($familyWhere) use ($auth) {
                                $familyWhere->where('owner_type', '=', Family::class)
                                            ->where('owner_id', '=', $auth->family->id);
                            });
                        });
                });
            })
            ->select('tag')
            ->distinct()
            ->get();
    }

    public function tasks(): MorphToMany
    {
        return $this->morphedByMany(Task::class, 'taggable');
    }

    public function recurringTasks(): MorphToMany
    {
        return $this->morphedByMany(RecurringTask::class, 'taggable');
    }
}
