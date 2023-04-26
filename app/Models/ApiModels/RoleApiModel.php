<?php

namespace App\Models\ApiModels;

use App\Repositories\Users\RolesRepository;
use Mbarclay36\LaravelCrud\Traits\IsApiModel;

class RoleApiModel
{
    use IsApiModel;

    protected static string $repositoryClass = RolesRepository::class;

    protected static array $apiModelAttributes = ['id', 'name'];
}
