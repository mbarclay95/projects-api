<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\ApiCrudController;
use App\Http\Requests\Users\UsersUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends ApiCrudController
{
    protected static string $modelClass = User::class;

    protected static array $indexRules = [];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var User[] $users */
        $users = User::query()
            ->with('roles')
            ->get();

        return new JsonResponse(User::toApiModels($users, ['clientPermissions', 'userConfig']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UsersUpdateRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validated();
        $user = User::query()->find($id);

        $user->name = $validated['name'];
        $user->userConfig->side_menu_open = $validated['userConfig']['sideMenuOpen'];
        $roles = Role::query()
                     ->whereIn('id', Collection::make($validated['roles'])->map(function ($role) {
                         return $role['id'];
                     }))
                     ->get();
        $user->syncRoles($roles);
        $user->save();
        $user->userConfig->save();

        if ($user->id == Auth::id()) {
            return new JsonResponse(User::toApiModel($user));
        }

        return new JsonResponse(User::toApiModel($user, ['clientPermissions', 'userConfig']));
    }
}
