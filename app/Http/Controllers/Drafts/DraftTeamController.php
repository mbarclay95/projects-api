<?php

namespace App\Http\Controllers\Drafts;

use App\Models\Drafts\DraftTeam;

class DraftTeamController extends DraftChildController
{
    protected static string $modelClass = DraftTeam::class;

    protected static array $storeRules = [
        'draftId' => 'required|integer',
        'name' => 'required|string',
        'sortOrder' => 'nullable|integer',
    ];

    protected static array $updateRules = [
        'name' => 'required|string',
        'sortOrder' => 'nullable|integer',
    ];
}
