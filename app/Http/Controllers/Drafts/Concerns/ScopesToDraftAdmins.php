<?php

namespace App\Http\Controllers\Drafts\Concerns;

use App\Models\Drafts\DraftAdmin;
use Illuminate\Contracts\Auth\Authenticatable;

trait ScopesToDraftAdmins
{
    protected function administers(?Authenticatable $user, ?int $draftId): bool
    {
        if (! $user || ! $draftId) {
            return false;
        }

        return DraftAdmin::query()
            ->where('draft_id', '=', $draftId)
            ->where('user_id', '=', $user->id)
            ->exists();
    }
}
