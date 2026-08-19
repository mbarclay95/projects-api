<?php

namespace App\Http\Controllers\Backups;

use App\Models\Backups\Schedule;
use Mbarclay36\LaravelCrud\CrudController;

class ScheduleController extends CrudController
{
    protected static string $modelClass = Schedule::class;

    protected static array $indexRules = [];

    protected static array $storeRules = [

    ];

    protected static array $updateRules = [];
}
