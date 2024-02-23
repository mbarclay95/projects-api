<?php

namespace App\Http\Controllers\Events;

use App\Models\Events\EventParticipant;
use Mbarclay36\LaravelCrud\CrudController;

class EventParticipantController extends CrudController
{
    protected static string $modelClass = EventParticipant::class;

    protected static array $indexRules = [];
    protected static array $storeRules = [];
    protected static array $updateRules = [
        'name' => 'string|required',
        'isGoing' => 'bool|required',
    ];
}
