<?php

namespace App\Models\ApiModels;

use Mbarclay36\LaravelCrud\Traits\HasApiModel;

class UserGroupMemberApiModel
{
    use HasApiModel;

    protected static array $apiModelAttributes = ['id', 'name'];
}
