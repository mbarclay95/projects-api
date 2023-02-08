<?php

namespace App\Models\Tasks;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TaskPoint
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @property string name
 * @property integer points
 *
 * @property integer family_id
 * @property Family family
 */
class TaskPoint extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'points'];
    protected static array $apiModelEntities = [];
    protected static array $apiModelArrayEntities = [];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public static function createEntity($request, User $auth): TaskPoint
    {
        $taskPoint = new TaskPoint([
            'name' => $request['name'],
            'points' => $request['points']
        ]);
        $taskPoint->family()->associate($request['familyId']);
        $taskPoint->save();

        return $taskPoint;
    }

    /**
     * @param TaskPoint $entity
     * @param $request
     * @param User $auth
     * @return Model|TaskPoint
     */
    public static function updateEntity(Model $entity, $request, User $auth): Model|TaskPoint
    {
        $entity->name = $request['name'];
        $entity->save();

        return $entity;
    }
}
