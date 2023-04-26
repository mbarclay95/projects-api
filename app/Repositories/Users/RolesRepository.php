<?php

namespace App\Repositories\Users;

use Mbarclay36\LaravelCrud\DefaultRepository;
use Spatie\Permission\Models\Role;

class RolesRepository extends DefaultRepository
{
    protected static string $modelClass = Role::class;
}
