<?php

namespace App\Http\Controllers\Backups;

use App\Models\Backups\Backup;
use Mbarclay36\LaravelCrud\CrudController;

class BackupController extends CrudController
{
    protected static string $modelClass = Backup::class;
    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
        'backupSteps' => 'required|array',
    ];
    protected static array $updateRules = [];
}
