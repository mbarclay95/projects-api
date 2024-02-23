<?php

namespace App\Traits;

use App\Enums\Permissions;
use App\Models\Users\User;

trait HasCrudPermissions
{
    protected static string $viewAnyPermission = Permissions::VIEW_ANY;
    protected static string $viewAnyForUserPermission = Permissions::VIEW_ANY_FOR_USER;
    protected static string $viewPermission = Permissions::VIEW;
    protected static string $viewForUserPermission = Permissions::VIEW_FOR_USER;
    protected static string $createPermission = Permissions::CREATE;
    protected static string $updatePermission = Permissions::UPDATE;
    protected static string $updateForUserPermission = Permissions::UPDATE_FOR_USER;
    protected static string $deletePermission = Permissions::DELETE;
    protected static string $deleteForUserPermission = Permissions::DELETE_FOR_USER;
    protected static string $restorePermission = Permissions::RESTORE;
    protected static string $restoreForUserPermission = Permissions::RESTORE_FOR_USER;

    public static function viewAnyPermission(): string
    {
        return static::class . static::$viewAnyPermission;
    }

    public static function viewAnyForUserPermission(): string
    {
        return static::class . static::$viewAnyForUserPermission;
    }

    public static function viewPermission(): string
    {
        return static::class . static::$viewPermission;
    }

    public static function viewForUserPermission(): string
    {
        return static::class . static::$viewForUserPermission;
    }

    public static function createPermission(): string
    {
        return static::class . static::$createPermission;
    }

    public static function updatePermission(): string
    {
        return static::class . static::$updatePermission;
    }

    public static function updateForUserPermission(): string
    {
        return static::class . static::$updateForUserPermission;
    }

    public static function deletePermission(): string
    {
        return static::class . static::$deletePermission;
    }

    public static function deleteForUserPermission(): string
    {
        return static::class . static::$deleteForUserPermission;
    }

    public static function restorePermission(): string
    {
        return static::class . static::$restorePermission;
    }

    public static function restoreForUserPermission(): string
    {
        return static::class . static::$restoreForUserPermission;
    }
}
