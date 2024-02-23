<?php

namespace App\Models\Users;

use App\Models\ApiModels\PermissionApiModel;
use App\Models\ApiModels\RoleApiModel;
use App\Models\Tasks\Family;
use App\Models\Tasks\TaskUserConfig;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Mbarclay36\LaravelCrud\Traits\IsApiModel;
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
 * @property string password
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
    use HasApiTokens, HasFactory, Notifiable, HasRoles, IsApiModel;

    protected static $unguarded = true;

    protected static array $apiModelAttributes = ['id', 'name', 'last_logged_in_at', 'family_id'];

    protected static array $apiModelEntities = [
        'userConfig' => UserConfig::class,
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

    public function createFirstUserConfig(string | null $homePage = null): UserConfig
    {
        $userConfig = new UserConfig([
            'side_menu_open' => true,
            'home_page_role' => $homePage
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

    public function getFamilyIdAttribute(): int | null
    {
        return $this->taskUserConfig?->family_id;
    }
}
