<?php

namespace App\Models\Events;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Event
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property boolean is_going
 * @property boolean notification_email
 *
 * @property integer event_id
 * @property Event event
 */
class EventParticipant extends BaseApiModel
{
    use HasFactory;

    protected static array $apiModelAttributes = ['id', 'name', 'is_going', 'event_id'];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * @param EventParticipant $entity
     * @param $request
     * @param User $auth
     * @return EventParticipant|Model
     * @throws AuthenticationException
     */
    public static function updateEntity(Model $entity, $request, User $auth): Model|EventParticipant
    {
        if ($entity->event->user_id !== $auth->id) {
            throw new AuthenticationException();
        }

        $entity->name = $request['name'];
        $entity->is_going = $request['isGoing'];
        $entity->save();

        return $entity;
    }

    /**
     * @param EventParticipant $entity
     * @param User $auth
     * @return void
     * @throws AuthenticationException
     */
    public static function destroyEntity(Model $entity, User $auth): void
    {
        if ($entity->event->user_id !== $auth->id) {
            throw new AuthenticationException();
        }

        $entity->delete();
    }
}
