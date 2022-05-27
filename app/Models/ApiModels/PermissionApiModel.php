<?php

namespace App\Models\ApiModels;

use App\Traits\HasApiModel;
use Illuminate\Database\Eloquent\Model;

class PermissionApiModel
{
    use HasApiModel;

    public static function toApiModel(Model $model): array|string
    {
        return $model->name;
    }
}
