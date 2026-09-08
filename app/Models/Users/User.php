<?php

namespace App\Models\Users;

use App\Models\ApiModels\PermissionApiModel;
use App\Models\ApiModels\RoleApiModel;
use App\Models\Tasks\TaskUserConfig;
use App\Models\UserGroups\UserGroup;
use App\Models\UserGroups\UserGroupUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Laravel\Sanctum\HasApiTokens;
use Mbarclay36\LaravelCrud\Traits\IsApiModel;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 *
 * @property int id
 * @property Carbon created_at
 * @property Carbon updated_at
 * @property Carbon last_logged_in_at
 * @property string name
 * @property string username
 * @property string password
 * @property UserConfig userConfig
 * @property TaskUserConfig taskUserConfig
 * @property Collection|Role[] roles
 * @property Collection|Permission[] rolePermissions
 * @property Collection|Permission[] clientPermissions
 * @property UserGroup taskGroup
 */
class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, HasRoles, IsApiModel, Notifiable;

    protected static $unguarded = true;

    protected static array $apiModelAttributes = ['id', 'name', 'last_logged_in_at', 'task_group_id'];

    protected static array $apiModelEntities = [
        'userConfig' => UserConfig::class,
    ];

    protected static array $apiModelArrayEntities = [
        'roles' => RoleApiModel::class,
        'clientPermissions' => PermissionApiModel::class,
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'last_logged_in_at' => 'datetime',
    ];

    public function userConfig(): HasOne
    {
        return $this->hasOne(UserConfig::class);
    }

    public function taskUserConfig(): HasOne
    {
        return $this->hasOne(TaskUserConfig::class);
    }

    public function taskGroup(): HasOneThrough
    {
        return $this->hasOneThrough(UserGroup::class, UserGroupUser::class, 'user_id', 'id', 'id', 'user_group_id')
            ->where('user_group_user.scope', 'tasks');
    }

    public function createFirstUserConfig(?string $homePage = null): UserConfig
    {
        $userConfig = new UserConfig([
            'side_menu_open' => true,
            'home_page_role' => $homePage,
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

    public function getTaskGroupIdAttribute(): ?int
    {
        return $this->taskGroup?->id;
    }
}
