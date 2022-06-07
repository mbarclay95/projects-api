<?php

namespace App\Models\ApiModels;

use App\Traits\HasApiModel;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class PermissionApiModel
{
    use HasApiModel;

    /**
     * @param Permission $model
     * @return array|string
     */
    public static function toApiModel(Model $model): array|string
    {
        return $model->name;
    }
}
