<?php

namespace App\Http\Controllers\Logging;

use App\Models\Logging\LogEvent;
use Mbarclay36\LaravelCrud\CrudController;

class LogEventController extends CrudController
{
    protected static string $modelClass = LogEvent::class;

    protected static array $indexRules = [];

    protected static array $storeRules = [];

    protected static array $updateRules = [];
}
