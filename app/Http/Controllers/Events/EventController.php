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

    protected static array $indexRules = [];
    protected static array $storeRules = [];
    protected static array $updateRules = [];
}
