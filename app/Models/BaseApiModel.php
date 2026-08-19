<?php

namespace App\Models;

use App\Traits\HasApiModel;
use App\Traits\HasCrudDestroyable;
use App\Traits\HasCrudIndexable;
use App\Traits\HasCrudPermissions;
use App\Traits\HasCrudShowable;
use App\Traits\HasCrudStorable;
use App\Traits\HasCrudUpdatable;
use Illuminate\Database\Eloquent\Model;

class BaseApiModel extends Model
{
    use HasApiModel, HasCrudDestroyable, HasCrudIndexable, HasCrudPermissions, HasCrudShowable, HasCrudStorable, HasCrudUpdatable;

    protected static $unguarded = true;
}
