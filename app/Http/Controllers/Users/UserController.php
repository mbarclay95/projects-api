<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\ApiCrudController;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class UserController extends ApiCrudController
{
    protected static string $modelClass = User::class;

    protected static array $indexRules = [];
    protected static array $storeRules = [
        'name' => 'required|string',
        'username' => 'required|string',
        'password' => 'required|string',
        'roles' => 'present|array',
        'userConfig.homePageRole' => 'required|string'
    ];
    protected static array $updateRules = [
        'name' => 'required|string',
        'roles' => 'present|array',
        'userConfig.sideMenuOpen' => 'required|bool',
        'userConfig.homePageRole' => 'required|string'
    ];

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate(static::$indexRules);

        /** @var User[] $users */
        $users = User::query()
                     ->with('roles', 'userConfig')
                     ->orderBy('id')
                     ->get();

        return new JsonResponse(User::toApiModels($users, ['clientPermissions', 'family_id', 'userConfig.money_app_token']));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function update(Request $request, int $id): JsonResponse
    {
        /** @var User $auth */
        $auth = Auth::user();
        if (!$auth->hasPermissionTo(User::updatePermission())) {
            throw new AuthenticationException();
        }
        $validated = $request->validate(static::$updateRules);

        /** @var User $user */
        $user = User::query()->with('userConfig')->find($id);

        $user->name = $validated['name'];
        $user->userConfig->side_menu_open = $validated['userConfig']['sideMenuOpen'];
        $user->userConfig->home_page_role = $validated['userConfig']['homePageRole'];
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
