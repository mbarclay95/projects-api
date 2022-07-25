<?php

namespace App\Models;

use App\Models\ApiModels\PermissionApiModel;
use App\Models\ApiModels\RoleApiModel;
use App\Models\Tasks\Family;
use App\Models\Tasks\TaskUserConfig;
use App\Traits\HasApiModel;
use App\Traits\HasCrudPermissions;
use App\Traits\HasCrudStorable;
use App\Traits\HasCrudUpdatable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * @property integer id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon last_logged_in_at
 *
 * @property string name
 * @property string username
 *
 * @property UserConfig userConfig
 * @property TaskUserConfig taskUserConfig
 *
 * @property Collection|Role[] roles
 * @property Collection|Permission[] rolePermissions
 * @property Collection|Permission[] clientPermissions
 * @property Family family
 */
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasApiModel, HasCrudStorable, HasCrudPermissions, HasCrudUpdatable;

    protected static $unguarded = true;

    protected static array $apiModelAttributes = ['id', 'name', 'last_logged_in_at'];

    protected static array $apiModelEntities = [
        'userConfig' => UserConfig::class,
        'taskUserConfig' => TaskUserConfig::class
    ];

    protected static array $apiModelArrayEntities = [
        'roles' => RoleApiModel::class,
        'clientPermissions' => PermissionApiModel::class
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    protected $dates = [
        'last_logged_in_at'
    ];

    public function userConfig(): HasOne
    {
        return $this->hasOne(UserConfig::class);
    }

    public function taskUserConfig(): HasOne
    {
        return $this->hasOne(TaskUserConfig::class);
    }

    public function family(): HasOneThrough
    {
        return $this->hasOneThrough(Family::class, TaskUserConfig::class, 'user_id', 'id', 'id', 'family_id');
    }

    public static function createEntity($request, User $auth): User
    {
        $user = new User([
            'name' => $request['name'],
            'username' => $request['username'],
            'password' => Hash::make($request['password']),
        ]);
        $user->save();
        $roles = Role::query()
                     ->whereIn('id', Collection::make($request['roles'])->map(function ($role) {
                         return $role['id'];
                     }))
                     ->get();
        $user->syncRoles($roles);

        return $user;
    }

    public function createFirstUserConfig(): UserConfig
    {
        $userConfig = new UserConfig([
            'side_menu_open' => true,
        ]);
        $userConfig->user()->associate($this);
        $userConfig->save();

        return $userConfig;
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    public function getRolePermissionsAttribute(): Collection
    {
        return $this->getAllPermissions();
    }

    public function getClientPermissionsAttribute(): Collection
    {
        return $this->getAllPermissions()
                    ->filter(function ($value) {
                        return str_contains($value, 'client_');
                    });
    }
}
