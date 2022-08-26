<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\ApiCrudController;
use App\Models\Events\EventParticipant;

class EventParticipantController extends ApiCrudController
{
    protected static string $modelClass = EventParticipant::class;

    protected static array $indexRules = [];
    protected static array $storeRules = [];
    protected static array $updateRules = [
        'name' => 'string|required',
        'isGoing' => 'bool|required',
    ];
}
