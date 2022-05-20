<?php

namespace App\Models\ApiModels;

use App\Models\HasApiModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;
use Spatie\Permission\Models\Role;

class PermissionApiModel
{
    use HasApiModel;

    public static function toApiModel(Model $model): array|string
    {
        return $model->name;
    }
}
