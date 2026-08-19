<?php

namespace App\Http\Controllers\Drafts;

use App\Http\Controllers\Drafts\Concerns\ScopesToDraftAdmins;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Mbarclay36\LaravelCrud\CrudController;

abstract class DraftChildController extends CrudController
{
    use ScopesToDraftAdmins;

    /**
     * cannotStore() receives no request and no model, only the user, so
     * there is nothing to scope a draft_admins check against there — the
     * check has to happen a level up, here.
     */
    public function store(Request $request): JsonResponse
    {
        if (! $this->administers(Auth::user(), $request->input('draftId'))) {
            throw new AuthenticationException;
        }

        return parent::store($request);
    }

    public function cannotUpdate(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(static::$modelClass::updatePermission())
            || ! $this->administers($user, $model->draft_id);
    }

    public function cannotDestroy(Authenticatable $user, Model $model): bool
    {
        return ! $user->hasPermissionTo(static::$modelClass::deletePermission())
            || ! $this->administers($user, $model->draft_id);
    }
}
