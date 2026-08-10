<?php

namespace App\Http\Controllers\Drafts;

use App\Models\Drafts\DraftAdmin;

class DraftAdminController extends DraftChildController
{
    protected static string $modelClass = DraftAdmin::class;

    protected static array $storeRules = [
        'draftId' => 'required|integer',
        'userId' => 'required|integer',
    ];
}
