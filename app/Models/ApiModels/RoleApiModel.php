<?php

namespace App\Models\ApiModels;

use App\Traits\HasApiModel;


class RoleApiModel
{
    use HasApiModel;

    protected static array $apiModelAttributes = ['id', 'name'];
}
