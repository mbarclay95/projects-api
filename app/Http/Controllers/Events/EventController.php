<?php

namespace App\Http\Controllers\Events;

use App\Models\Events\Event;
use Mbarclay36\LaravelCrud\CrudController;

class EventController extends CrudController
{
    protected static string $modelClass = Event::class;

    protected static array $indexRules = [
        'showArchived' => 'required|bool',
        'search' => 'nullable|string'
    ];
    protected static array $storeRules = [
        'name' => 'required|string',
        'notes' => 'present|string|nullable',
        'eventDate' => 'required|date',
        'numOfPeople' => 'required|int',
        'limitParticipants' => 'required|bool',
        'notificationEmail' => 'string|nullable'
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'notes' => 'present|string|nullable',
        'eventDate' => 'required|date',
        'numOfPeople' => 'required|int',
        'limitParticipants' => 'required|bool',
        'notificationEmail' => 'string|nullable'
    ];
}
