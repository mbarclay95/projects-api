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
    protected static string $model = User::class;

    protected static array $indexRules = [];

    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

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
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UsersUpdateRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function update(UsersUpdateRequest $request, User $user): JsonResponse
    {
        $validated = $request->validated();

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

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user)
    {
        //
    }
}
