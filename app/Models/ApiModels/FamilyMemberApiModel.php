<?php

namespace App\Models\ApiModels;

use Mbarclay36\LaravelCrud\Traits\HasApiModel;

class FamilyMemberApiModel
{
    use HasApiModel;

    protected static array $apiModelAttributes = ['id', 'name'];
}
