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
 *
 * @property integer user_id
 * @property User user
 *
 * @property Collection|EventParticipant[] eventParticipants
 */
class Event extends BaseApiModel
{
    use HasFactory, SoftDeletes, Filterable;

    protected static array $apiModelAttributes = ['id', 'name', 'notes', 'event_date', 'num_of_people', 'token', 'deleted_at'];
    protected static array $apiModelEntities = [];
    protected static array $apiModelArrayEntities = [
        'eventParticipants' => EventParticipant::class
    ];

    protected $dates = [
        'event_date'
    ];

    protected $dateFormat = 'Y-m-d H:i:sO';

    public static function getUserEntities($request, User $auth)
    {
        return Event::query()
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
            'token' => Str::random(),
        ]);
        $event->user()->associate($auth);
        $event->save();

        return $event;
    }

    /**
     * @param Event $entity
     * @param $request
     * @param User $auth
     * @return Event
     */
    public static function updateUserEntity(Model $entity, $request, User $auth): Model
    {
        $entity->name = $request['name'];
        $entity->notes = $request['notes'];
        $entity->event_date = $request['eventDate'];
        $entity->num_of_people = $request['numOfPeople'];
        $entity->save();

        return $entity;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventParticipants(): HasMany
    {
        return $this->hasMany(EventParticipant::class);
    }
}
