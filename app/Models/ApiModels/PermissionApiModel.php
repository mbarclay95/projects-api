<?php

namespace App\Models\ApiModels;

use Illuminate\Database\Eloquent\Model;
use Mbarclay36\LaravelCrud\Traits\HasApiModel;

class PermissionApiModel
{
    use HasApiModel;

    public static function toApiModel(?Model $model, array $hideItem = []): array|null|string
    {
        return $model->name;
    }
}
