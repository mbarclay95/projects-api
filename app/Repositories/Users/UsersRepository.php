<?php

namespace App\Repositories\Users;

use App\Models\Users\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Mbarclay36\LaravelCrud\DefaultRepository;
use Spatie\Permission\Models\Role;

class UsersRepository extends DefaultRepository
{
    /**
     * @param $request
     * @param Authenticatable $user
     * @param bool $viewOnlyForUser
     * @return Collection|array
     */
    public function getEntities($request, Authenticatable $user, bool $viewOnlyForUser): Collection|array
    {
        /** @var User[] $users */
        $users = User::query()
                     ->with('roles', 'userConfig')
                     ->orderBy('id')
                     ->get();

        return User::toApiModels($users, ['clientPermissions', 'family_id', 'userConfig.money_app_token']);
    }

    public function createEntity($request, Authenticatable $user): Model|array
    {
        $model = new User([
            'name' => $request['name'],
            'username' => $request['username'],
            'password' => Hash::make($request['password']),
        ]);
        $model->save();
        $roles = Role::query()
                     ->whereIn('id', Collection::make($request['roles'])->map(function ($role) {
                         return $role['id'];
                     }))
                     ->get();
        $model->syncRoles($roles);
        $model->createFirstUserConfig($request['userConfig']['homePageRole']);

        return $model;
    }

    /**
     * @param User $model
     * @param $request
     * @param Authenticatable $user
     * @return Model|array
     */
    public function updateEntity(Model $model, $request, Authenticatable $user): Model|array
    {
        $model->name = $request['name'];
        $model->userConfig->side_menu_open = $request['userConfig']['sideMenuOpen'];
        $model->userConfig->home_page_role = $request['userConfig']['homePageRole'];
        $roles = Role::query()
                     ->whereIn('id', Collection::make($request['roles'])->map(function ($role) {
                         return $role['id'];
                     }))
                     ->get();
        $model->syncRoles($roles);
        $model->save();
        $model->userConfig->save();

        if ($model->id == $user->id) {
            return $model;
        }

        return User::toApiModel($model, ['clientPermissions', 'userConfig']);
    }
}
