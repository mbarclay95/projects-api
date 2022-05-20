<?php

namespace App\Models\ApiModels;

use App\Models\HasApiModel;


class RoleApiModel
{
    use HasApiModel;

    protected static array $apiModelAttributes = ['id', 'name'];
}
