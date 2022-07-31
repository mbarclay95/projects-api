<?php

namespace App\Models\Events;

use App\Models\BaseApiModel;
use App\Models\User;
use Carbon\Carbon;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class Event
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon deleted_at
 *
 * @property string name
 * @property string notes
 * @property Carbon event_date
 * @property integer num_of_people
 * @property string token
 * @property boolean limit_participants
 * @property boolean notification_email
 *
 * @property integer user_id
 * @property User user
 *
 * @property Collection|EventParticipant[] eventParticipants
 */
class Event extends BaseApiModel
{
    use HasFactory, SoftDeletes, Filterable;

    protected static array $apiModelAttributes = ['id', 'name', 'notes', 'event_date', 'num_of_people', 'token',
        'deleted_at', 'limit_participants', 'notification_email'];
    protected static array $apiModelEntities = [];
    protected static array $apiModelArrayEntities = [
        'eventParticipants' => EventParticipant::class
    ];

    protected $dates = [
        'event_date'
    ];

    protected $dateFormat = 'Y-m-d H:i:sO';

    public static function getEntities($request, User $auth, bool $viewAnyForUser)
    {
        return Event::query()
                    ->with('eventParticipants')
                    ->where('user_id', '=', $auth->id)
                    ->orderBy('event_date')
                    ->filter($request)
                    ->get();
    }

    public static function createEntity($request, User $auth): Event
    {
        $event = new Event([
            'name' => $request['name'],
            'notes' => $request['notes'],
            'event_date' => $request['eventDate'],
            'num_of_people' => $request['numOfPeople'],
            'limit_participants' => $request['limitParticipants'],
            'notification_email' => $request['notificationEmail'],
            'token' => Str::random(),
        ]);
        $event->user()->associate($auth);
        $event->save();

        return $event;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param Event $entity
     * @param $request
     * @param User $auth
     * @return Event
     */
    public static function updateEntity(Model $entity, $request, User $auth): Model
    {
        $entity->name = $request['name'];
        $entity->notes = $request['notes'];
        $entity->event_date = $request['eventDate'];
        $entity->num_of_people = $request['numOfPeople'];
        $entity->limit_participants = $request['limitParticipants'];
        $entity->notification_email = $request['notificationEmail'];
        $entity->save();

        return $entity;
    }

    public function eventParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }
}
