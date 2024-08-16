<?php

namespace App\Http\Controllers\Backups;

use App\Models\Backups\Target;
use Mbarclay36\LaravelCrud\CrudController;

class TargetController extends CrudController
{
    protected static string $modelClass = Target::class;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
        'targetUrl' => 'required|string',
        'hostName' => 'nullable|string',
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'targetUrl' => 'required|string',
        'hostName' => 'nullable|string',
    ];
}
