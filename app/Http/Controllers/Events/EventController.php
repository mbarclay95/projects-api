<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\ApiCrudController;
use App\Models\Events\Event;

class EventController extends ApiCrudController
{
    protected static string $modelClass = Event::class;

    protected static bool $getUserEntitiesOnly = true;
    protected static bool $getUserEntityOnly = true;
    protected static bool $updateUserEntityOnly = true;
    protected static bool $destroyUserEntityOnly = true;

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
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'notes' => 'present|string|nullable',
        'eventDate' => 'required|date',
        'numOfPeople' => 'required|int',
        'limitParticipants' => 'required|bool',
    ];
}
